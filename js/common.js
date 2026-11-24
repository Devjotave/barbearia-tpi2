async function requisicao(url, opcoes = {}) {
    const resposta = await fetch(url, {
        credentials: 'same-origin',
        ...opcoes
    });

    let retorno;
    try {
        retorno = await resposta.json();
    } catch (erro) {
        throw new Error('Resposta inválida do servidor.');
    }

    if (resposta.status === 401) {
        window.location.href = 'login.html';
        throw new Error('Sessão expirada.');
    }

    if (!resposta.ok || retorno.sucesso === false) {
        throw new Error(retorno.mensagem || 'Não foi possível concluir a operação.');
    }

    return retorno;
}

async function verificarSessao() {
    const protegido = document.body.dataset.protected === 'true';
    const somenteAdmin = document.body.dataset.adminOnly === 'true';

    try {
        const resposta = await fetch('php/sessao.php', { credentials: 'same-origin' });
        const retorno = await resposta.json();

        if (!retorno.autenticado) {
            if (protegido) {
                window.location.href = 'login.html';
                return null;
            }
            return null;
        }

        window.usuarioLogado = retorno.usuario;

        document.querySelectorAll('[data-user-name]').forEach((elemento) => {
            elemento.textContent = retorno.usuario.nome;
        });

        document.querySelectorAll('[data-user-role]').forEach((elemento) => {
            elemento.textContent = retorno.usuario.perfil;
        });

        if (retorno.usuario.perfil !== 'Administrador') {
            document.querySelectorAll('[data-admin-link]').forEach((elemento) => elemento.remove());
            if (somenteAdmin) {
                window.location.href = 'index.html';
                return null;
            }
        }

        return retorno.usuario;
    } catch (erro) {
        if (protegido) {
            window.location.href = 'login.html';
        }
        return null;
    }
}

async function sair() {
    try {
        await fetch('php/logout.php', { method: 'POST', credentials: 'same-origin' });
    } finally {
        window.location.href = 'login.html';
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    document.querySelectorAll('[data-logout]').forEach((botao) => {
        botao.addEventListener('click', sair);
    });

    const usuario = await verificarSessao();
    document.dispatchEvent(new CustomEvent('sessao-pronta', { detail: usuario }));
});
