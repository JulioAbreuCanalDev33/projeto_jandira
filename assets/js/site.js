/* Projeto Jandira — interações leves do front-end */

(function () {
    "use strict";

    // Tema claro/escuro com persistência local
    const root = document.documentElement;
    const themeToggles = document.querySelectorAll("[data-theme-toggle]");
    const themeColorMeta = document.querySelector('meta[name="theme-color"]');

    const syncThemeControls = function () {
        const isDark = root.getAttribute("data-theme") === "dark";

        themeToggles.forEach(function (button) {
            button.setAttribute("aria-pressed", isDark ? "true" : "false");
            button.setAttribute("aria-label", isDark ? "Ativar tema claro" : "Ativar tema escuro");
            button.setAttribute("title", isDark ? "Ativar tema claro" : "Ativar tema escuro");

            const icon = button.querySelector("i");
            if (icon) {
                icon.classList.toggle("bi-moon-stars", !isDark);
                icon.classList.toggle("bi-sun", isDark);
            }
        });

        if (themeColorMeta) {
            themeColorMeta.setAttribute("content", isDark ? "#0b1220" : "#0b4ea2");
        }
    };

    const applyTheme = function (theme, persist) {
        root.setAttribute("data-theme", theme);
        root.setAttribute("data-bs-theme", theme);

        if (persist) {
            try {
                window.localStorage.setItem("jandira-theme", theme);
            } catch (error) {
                // Armazenamento indisponível: o tema continua válido nesta sessão.
            }
        }

        syncThemeControls();
    };

    themeToggles.forEach(function (button) {
        button.addEventListener("click", function () {
            const nextTheme = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
            applyTheme(nextTheme, true);
        });
    });

    syncThemeControls();

    // Busca instantânea e filtro por modalidade nas tabelas
    document.querySelectorAll("[data-table-search]").forEach(function (input) {
        const targetSelector = input.getAttribute("data-table-target");
        const table = targetSelector ? document.querySelector(targetSelector) : null;

        if (!table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll("tbody tr[data-search]"));
        const emptyRow = table.querySelector("[data-empty-search]");
        const counter = document.querySelector("[data-result-count]");
        const filters = Array.from(document.querySelectorAll("[data-table-filter]")).filter(function (select) {
            return select.getAttribute("data-table-target") === targetSelector;
        });

        const applyFilter = function () {
            const term = input.value.trim().toLowerCase();
            let visible = 0;

            rows.forEach(function (row) {
                const haystack = (row.getAttribute("data-search") || row.textContent).toLowerCase();
                const modalidadeEsporte = row.getAttribute("data-modalidade-esporte") || "";
                const matchesTerm = term === "" || haystack.indexOf(term) !== -1;
                const matchesFilters = filters.every(function (select) {
                    const value = select.value;

                    if (value === "") {
                        return true;
                    }

                    if (select.hasAttribute("data-modalidade-esporte-filter")) {
                        return modalidadeEsporte === value;
                    }

                    return true;
                });
                const match = matchesTerm && matchesFilters;

                row.classList.toggle("d-none", !match);

                if (match) {
                    visible += 1;
                }
            });

            if (emptyRow) {
                emptyRow.classList.toggle("d-none", visible > 0);
            }

            if (counter) {
                counter.textContent = visible === 1
                    ? "1 registro encontrado"
                    : visible + " registros encontrados";
            }
        };

        input.addEventListener("input", applyFilter);
        filters.forEach(function (select) {
            select.addEventListener("change", applyFilter);
        });
        applyFilter();
    });

    // Busca instantânea na galeria de arquivos
    document.querySelectorAll("[data-file-search]").forEach(function (input) {
        const targetSelector = input.getAttribute("data-file-target");
        const gallery = targetSelector ? document.querySelector(targetSelector) : null;

        if (!gallery) {
            return;
        }

        const cards = Array.from(gallery.querySelectorAll(".file-card[data-search]"));
        const emptyState = document.querySelector("[data-file-empty]");
        const counter = document.querySelector("[data-file-count]");
        const total = cards.length;

        const applyFilter = function () {
            const term = input.value.trim().toLowerCase();
            let visible = 0;

            cards.forEach(function (card) {
                const haystack = (card.getAttribute("data-search") || card.textContent).toLowerCase();
                const match = term === "" || haystack.indexOf(term) !== -1;
                card.classList.toggle("d-none", !match);

                if (match) {
                    visible += 1;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle("d-none", visible > 0 || total === 0);
            }

            if (counter) {
                counter.textContent = visible === 1
                    ? "1 arquivo encontrado"
                    : visible + " arquivos encontrados";
            }
        };

        input.addEventListener("input", applyFilter);
        applyFilter();
    });

    // Confirmação de ações destrutivas
    document.querySelectorAll("[data-confirm]").forEach(function (element) {
        element.addEventListener("click", function (event) {
            const message = element.getAttribute("data-confirm") || "Tem certeza que deseja continuar?";
            if (!window.confirm(message)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    // Pré-visualização da foto selecionada
    document.querySelectorAll("[data-photo-input]").forEach(function (input) {
        const targetSelector = input.getAttribute("data-photo-input");
        const preview = targetSelector ? document.querySelector(targetSelector) : null;
        const placeholderSelector = input.getAttribute("data-photo-placeholder");
        const placeholder = placeholderSelector ? document.querySelector(placeholderSelector) : null;

        if (!preview) {
            return;
        }

        input.addEventListener("change", function () {
            const file = input.files && input.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = String(event.target.result);
                preview.classList.remove("d-none");

                if (placeholder) {
                    placeholder.classList.add("d-none");
                }
            };
            reader.readAsDataURL(file);
        });
    });

    // Mostrar/ocultar senha
    document.querySelectorAll("[data-toggle-password]").forEach(function (button) {
        const targetSelector = button.getAttribute("data-toggle-password");
        const input = targetSelector ? document.querySelector(targetSelector) : null;

        if (!input) {
            return;
        }

        button.addEventListener("click", function () {
            const isPassword = input.getAttribute("type") === "password";
            input.setAttribute("type", isPassword ? "text" : "password");

            const icon = button.querySelector("i");
            if (icon) {
                icon.classList.toggle("bi-eye", !isPassword);
                icon.classList.toggle("bi-eye-slash", isPassword);
            }

            button.setAttribute("aria-label", isPassword ? "Ocultar senha" : "Mostrar senha");
        });
    });

    // Máscaras simples de CPF, telefone e CEP
    const onlyDigits = function (value) {
        return value.replace(/\D/g, "");
    };

    const maskCpf = function (value) {
        const digits = onlyDigits(value).slice(0, 11);
        return digits
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    };

    const maskCep = function (value) {
        const digits = onlyDigits(value).slice(0, 8);
        return digits.replace(/(\d{5})(\d)/, "$1-$2");
    };

    const maskPhone = function (value) {
        const digits = onlyDigits(value).slice(0, 11);

        if (digits.length <= 10) {
            return digits
                .replace(/^(\d{2})(\d)/, "($1) $2")
                .replace(/(\d{4})(\d)/, "$1-$2");
        }

        return digits
            .replace(/^(\d{2})(\d)/, "($1) $2")
            .replace(/(\d{5})(\d)/, "$1-$2");
    };

    document.querySelectorAll("[data-mask]").forEach(function (input) {
        const type = input.getAttribute("data-mask");

        input.addEventListener("input", function () {
            if (type === "cpf") {
                input.value = maskCpf(input.value);
            } else if (type === "cep") {
                input.value = maskCep(input.value);
            } else if (type === "phone") {
                input.value = maskPhone(input.value);
            }
        });
    });
})();
