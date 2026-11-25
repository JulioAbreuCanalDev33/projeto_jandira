<?php

require_once __DIR__ . '/../includes/supabase.php';

$db = supabase_db();

function formatar_data($data){
	return implode('/', array_reverse(explode('-', $data)));
}

function formatar_telefone($telefone) {
	// Verifica se o telefone já está no formato esperado
    if (preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $telefone)) {
		return $telefone; // Retorna o número sem modificar
    }
	
    // Remove qualquer caractere não numérico
    $telefone = preg_replace('/\D/', '', $telefone);

    // Verifica se o número possui o tamanho correto
    if (strlen($telefone) === 11) {
        $ddd = substr($telefone, 0, 2);
        $parte1 = substr($telefone, 2, 5);
        $parte2 = substr($telefone, 7);
        return "($ddd) $parte1-$parte2";
    } elseif (strlen($telefone) === 10) {
		$ddd = substr($telefone, 0, 2);
        $parte1 = substr($telefone, 2, 4);
        $parte2 = substr($telefone, 6);
        return "($ddd) $parte1-$parte2";
    }

    // Retorna o número original se ele não tiver o tamanho correto
    return $telefone;
}

/*function formatar_telefone($telefone){
	$ddd = substr($telefone, 0, 2);
	$parte1 = substr($telefone, 2, 5);
	$parte2 = substr($telefone, 7);
	return "($ddd) $parte1-$parte2";
}*/

function formatar_cep($cep) {
	$pedaco1 = substr($cep, 0, 5);
	$pedaco2 = substr($cep, 5);
	return "$pedaco1-$pedaco2";
}

function formatar_cpf($cpf) {
	$part1 = substr($cpf, 0, 3);
	$part2 = substr($cpf, 3, 3);
	$part3 = substr($cpf, 6, 3);
	$part4 = substr($cpf, 9);
	return "$part1.$part2.$part3-$part4";
}

function buscar_modalidades_esporte(SupabaseDb $db): array {
	return $db->select('modalidades_esporte', 'id, nome', [], 'nome ASC');
}

function modalidade_esporte_existe(SupabaseDb $db, int $id): bool {
	if ($id <= 0) {
		return false;
	}

	return $db->count('modalidades_esporte', ['id' => $id]) > 0;
}

/**
 * Envia uma imagem para o Supabase Storage e devolve a URL pública.
 */
function salvar_imagem(SupabaseDb $db, array $arquivo, string $pasta): string {
	$conteudo = file_get_contents($arquivo['tmp_name']);
	if ($conteudo === false) {
		throw new RuntimeException('Não foi possível ler a imagem enviada.');
	}

	$extensao = strtolower(pathinfo((string) $arquivo['name'], PATHINFO_EXTENSION));
	$objeto = $pasta . '/' . bin2hex(random_bytes(16)) . '.' . $extensao;

	return $db->storageUpload('arquivos', $objeto, $conteudo, (string) mime_content_type($arquivo['tmp_name']));
}

function remover_imagem(SupabaseDb $db, string $url): void {
	if ($url !== '') {
		$db->removerArquivoPublico($url);
	}
}
