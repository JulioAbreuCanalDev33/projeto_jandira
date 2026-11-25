<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Cliente mínimo da API REST do Supabase (PostgREST) e do Supabase Storage.
 *
 * Todas as chamadas usam a chave secreta definida no servidor (SUPABASE_SERVICE_KEY),
 * que nunca é enviada ao navegador. As tabelas do Jandira têm RLS ativado e
 * somente essa chave consegue ler ou alterar os dados.
 */
final class SupabaseDb
{
    private string $url;
    private string $key;

    public function __construct(?string $url = null, ?string $key = null)
    {
        $this->url = rtrim(trim((string) ($url ?? env('SUPABASE_URL', ''))), '/');
        $this->key = trim((string) ($key ?? env('SUPABASE_SERVICE_KEY', '')));

        if ($this->url === '' || $this->key === '') {
            throw new RuntimeException(
                'Configure SUPABASE_URL e SUPABASE_SERVICE_KEY no ambiente da aplicação.'
            );
        }
    }

    /**
     * Executa uma requisição HTTP e devolve status, corpo e cabeçalhos.
     *
     * @return array{status:int, body:string, headers:array<string,string>}
     */
    private function request(
        string $method,
        string $path,
        array $query = [],
        mixed $body = null,
        array $extraHeaders = []
    ): array {
        $url = $this->url . '/' . ltrim($path, '/');
        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Accept: application/json',
        ];

        if (is_array($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $headers[] = 'Content-Type: application/json';
        }

        foreach ($extraHeaders as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        if (is_string($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);

        if ($raw === false) {
            $erro = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Não foi possível conectar ao Supabase: ' . $erro);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $cabecalhos = [];
        foreach (preg_split('/\r?\n/', substr($raw, 0, $headerSize)) ?: [] as $linha) {
            if (str_contains($linha, ':')) {
                [$nome, $valor] = explode(':', $linha, 2);
                $cabecalhos[strtolower(trim($nome))] = trim($valor);
            }
        }

        $corpo = substr($raw, $headerSize);

        if ($status >= 400) {
            throw new RuntimeException(
                'Supabase respondeu HTTP ' . $status . ': ' . mb_substr($corpo, 0, 300)
            );
        }

        return ['status' => $status, 'body' => $corpo, 'headers' => $cabecalhos];
    }

    private function decode(string $body): array
    {
        if ($body === '') {
            return [];
        }

        $dados = json_decode($body, true);

        return is_array($dados) ? $dados : [];
    }

    private static function filtros(array $filtros): array
    {
        $normalizados = [];

        foreach ($filtros as $campo => $valor) {
            if ($valor === null) {
                $normalizados[$campo] = 'is.null';
            } elseif (is_bool($valor)) {
                $normalizados[$campo] = 'is.' . ($valor ? 'true' : 'false');
            } elseif (is_int($valor) || is_float($valor)) {
                $normalizados[$campo] = 'eq.' . $valor;
            } elseif (is_string($valor) && preg_match('/^(eq|neq|gt|gte|lt|lte|like|ilike|in|is|cs|cd)\./', $valor)) {
                $normalizados[$campo] = $valor;
            } else {
                $normalizados[$campo] = 'eq.' . (string) $valor;
            }
        }

        return $normalizados;
    }

    /**
     * Converte "nome ASC, id DESC" para o formato do PostgREST: "nome.asc,id.desc".
     */
    private static function ordem(string $ordem): string
    {
        $partes = [];

        foreach (explode(',', $ordem) as $item) {
            $item = trim($item);

            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s+(asc|desc)$/i', $item, $partesOrdem)) {
                $partes[] = $partesOrdem[1] . '.' . strtolower($partesOrdem[2]);
            } else {
                $partes[] = $item;
            }
        }

        return implode(',', $partes);
    }

    public function select(
        string $tabela,
        string $colunas = '*',
        array $filtros = [],
        ?string $ordem = null,
        ?int $limite = null
    ): array {
        $query = ['select' => $colunas] + self::filtros($filtros);

        if ($ordem !== null) {
            $query['order'] = self::ordem($ordem);
        }
        if ($limite !== null) {
            $query['limit'] = (string) $limite;
        }

        return $this->decode($this->request('GET', 'rest/v1/' . $tabela, $query)['body']);
    }

    public function selectOne(string $tabela, string $colunas = '*', array $filtros = []): ?array
    {
        $registros = $this->select($tabela, $colunas, $filtros, null, 1);

        return $registros[0] ?? null;
    }

    public function insert(string $tabela, array $valores): array
    {
        $resposta = $this->request(
            'POST',
            'rest/v1/' . $tabela,
            [],
            [$valores],
            ['Prefer' => 'return=representation']
        );

        return $this->decode($resposta['body']);
    }

    public function upsert(string $tabela, array $valores): array
    {
        $resposta = $this->request(
            'POST',
            'rest/v1/' . $tabela,
            [],
            [$valores],
            ['Prefer' => 'resolution=merge-duplicates,return=representation']
        );

        return $this->decode($resposta['body']);
    }

    public function update(string $tabela, array $valores, array $filtros): array
    {
        $resposta = $this->request(
            'PATCH',
            'rest/v1/' . $tabela,
            self::filtros($filtros),
            $valores,
            ['Prefer' => 'return=representation']
        );

        return $this->decode($resposta['body']);
    }

    public function delete(string $tabela, array $filtros): int
    {
        $resposta = $this->request(
            'DELETE',
            'rest/v1/' . $tabela,
            self::filtros($filtros),
            null,
            ['Prefer' => 'return=representation']
        );

        return count($this->decode($resposta['body']));
    }

    public function count(string $tabela, array $filtros = []): int
    {
        $query = self::filtros($filtros);
        $resposta = $this->request('GET', 'rest/v1/' . $tabela, $query, null, [
            'Prefer' => 'count=exact',
            'Range' => '0-0',
        ]);

        $contentRange = $resposta['headers']['content-range'] ?? '';
        if (preg_match('/\/\s*(\d+)\s*$/', $contentRange, $partes)) {
            return (int) $partes[1];
        }

        return count($this->decode($resposta['body']));
    }

    public function storagePublicUrl(string $bucket, string $objeto): string
    {
        $partes = array_map('rawurlencode', explode('/', ltrim($objeto, '/')));

        return $this->url . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . implode('/', $partes);
    }

    public function storageUpload(string $bucket, string $objeto, string $conteudo, string $contentType): string
    {
        $caminho = 'storage/v1/object/' . rawurlencode($bucket) . '/'
            . implode('/', array_map('rawurlencode', explode('/', ltrim($objeto, '/'))));

        $this->request('POST', $caminho, [], $conteudo, [
            'Content-Type' => $contentType,
            'x-upsert' => 'true',
        ]);

        return $this->storagePublicUrl($bucket, $objeto);
    }

    public function storageDelete(string $bucket, string $objeto): bool
    {
        $caminho = 'storage/v1/object/' . rawurlencode($bucket) . '/'
            . implode('/', array_map('rawurlencode', explode('/', ltrim($objeto, '/'))));

        try {
            $this->request('DELETE', $caminho);
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * Remove o objeto do Storage a partir da URL pública salva no banco.
     */
    public function removerArquivoPublico(string $url): bool
    {
        $prefixo = '/storage/v1/object/public/arquivos/';
        $caminho = parse_url($url, PHP_URL_PATH);
        if (!is_string($caminho) || !str_contains($caminho, $prefixo)) {
            return false;
        }

        $objeto = urldecode(substr($caminho, strpos($caminho, $prefixo) + strlen($prefixo)));
        if ($objeto === '' || str_contains($objeto, '..')) {
            return false;
        }

        return $this->storageDelete('arquivos', $objeto);
    }
}

function supabase_db(): SupabaseDb
{
    static $instancia = null;

    if ($instancia === null) {
        $instancia = new SupabaseDb();
    }

    return $instancia;
}

/**
 * Sessões gravadas no banco: necessário porque a Vercel executa cada
 * requisição em uma função isolada, sem sistema de arquivos persistente.
 */
final class SupabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private SupabaseDb $db)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            $sessao = $this->db->selectOne('sessoes', 'dados', ['id' => $id]);
            return (string) ($sessao['dados'] ?? '');
        } catch (Throwable) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $this->db->upsert('sessoes', [
                'id' => $id,
                'dados' => $data,
                'atualizado_em' => gmdate('Y-m-d\TH:i:sP'),
            ]);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $this->db->delete('sessoes', ['id' => $id]);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $limite = gmdate('Y-m-d\TH:i:sP', time() - $max_lifetime);
            $expiradas = $this->db->select('sessoes', 'id', ['atualizado_em' => 'lt.' . $limite]);

            foreach ($expiradas as $sessao) {
                $this->db->delete('sessoes', ['id' => (string) $sessao['id']]);
            }

            return count($expiradas);
        } catch (Throwable) {
            return 0;
        }
    }
}
