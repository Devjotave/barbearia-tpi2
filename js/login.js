const formLogin = document.getElementById('formLogin');
const mensagemLogin = document.getElementById('mensagemLogin');
const campoSenha = document.getElementById('senha');
const mostrarSenha = document.getElementById('mostrarSenha');

function mensagem(texto, tipo) {
    mensagemLogin.textContent = texto;
    mensagemLogin.className = `mensagem campo-largo ${tipo}`;
}

function configurarExibicaoSenha() {
    if (!campoSenha || !mostrarSenha) {
        return;
    }

    mostrarSenha.addEventListener('change', () => {
        campoSenha.type = mostrarSenha.checked ? 'text' : 'password';
    });
}

async function verificarLoginExistente() {
    try {
        const resposta = await fetch('php/sessao.php', { credentials: 'same-origin' });
        const retorno = await resposta.json();

        if (retorno.autenticado) {
            window.location.href = 'index.html';
        }
    } catch (erro) {
        // A tela continua disponível para nova tentativa de login.
    }
}

formLogin.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    if (!formLogin.checkValidity()) {
        formLogin.reportValidity();
        return;
    }

    const botao = formLogin.querySelector('button[type="submit"]');
    botao.disabled = true;
    mensagem('Validando acesso...', 'info');

    try {
        const resposta = await fetch('php/login.php', {
            method: 'POST',
            body: new FormData(formLogin),
            credentials: 'same-origin'
        });

        const retorno = await resposta.json();

        if (!resposta.ok || !retorno.sucesso) {
            throw new Error(retorno.mensagem || 'Não foi possível realizar o login.');
        }

        mensagem('Login realizado com sucesso.', 'sucesso');
        window.location.href = 'index.html';
    } catch (erro) {
        mensagem(erro.message, 'erro');
    } finally {
        botao.disabled = false;
    }
});

configurarExibicaoSenha();
verificarLoginExistente();
