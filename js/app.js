const formulario = document.querySelector('.formulario');
const mensagem = document.querySelector('.mensagem');

function mostrarMensagem(texto, tipo) {
    if (!mensagem) {
        return;
    }

    mensagem.textContent = texto;
    mensagem.className = `mensagem campo-largo ${tipo}`;
}

function somenteNumeros(valor) {
    return valor.replace(/\D/g, '');
}

function mascararCpf(valor) {
    const numeros = somenteNumeros(valor).slice(0, 11);

    return numeros
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

function mascararTelefone(valor) {
    const numeros = somenteNumeros(valor).slice(0, 11);

    if (numeros.length <= 10) {
        return numeros
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    }

    return numeros
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d)/, '$1-$2');
}

function configurarMascaras() {
    document.querySelectorAll('[data-mask="cpf"]').forEach((campo) => {
        campo.addEventListener('input', () => {
            campo.value = mascararCpf(campo.value);
        });
    });

    document.querySelectorAll('[data-mask="telefone"]').forEach((campo) => {
        campo.addEventListener('input', () => {
            campo.value = mascararTelefone(campo.value);
        });
    });
}

function configurarDataMinima() {
    document.querySelectorAll('[data-min-today]').forEach((campo) => {
        const hoje = new Date();
        const ano = hoje.getFullYear();
        const mes = String(hoje.getMonth() + 1).padStart(2, '0');
        const dia = String(hoje.getDate()).padStart(2, '0');
        campo.min = `${ano}-${mes}-${dia}`;
    });
}

async function enviarFormulario(evento) {
    evento.preventDefault();

    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return;
    }

    const botaoEnviar = formulario.querySelector('button[type="submit"]');
    const endpoint = formulario.dataset.endpoint;
    const dados = new FormData(formulario);

    botaoEnviar.disabled = true;
    mostrarMensagem('Enviando dados...', 'sucesso');

    try {
        const resposta = await fetch(endpoint, {
            method: 'POST',
            body: dados
        });

        const retorno = await resposta.json();

        if (!resposta.ok || !retorno.sucesso) {
            mostrarMensagem(retorno.mensagem || 'Não foi possível enviar os dados.', 'erro');
            return;
        }

        mostrarMensagem(retorno.mensagem, 'sucesso');
        formulario.reset();
        configurarDataMinima();
    } catch (erro) {
        mostrarMensagem('Falha de comunicação com o servidor. Execute o projeto pelo PHP ou XAMPP.', 'erro');
    } finally {
        botaoEnviar.disabled = false;
    }
}

configurarMascaras();
configurarDataMinima();

if (formulario) {
    formulario.addEventListener('submit', enviarFormulario);
}
