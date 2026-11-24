const modulo = document.body.dataset.module;
const formulario = document.querySelector('.formulario');
const mensagem = document.querySelector('.mensagem');
const corpoTabela = document.getElementById('listaRegistros');
let registros = [];

const configuracoes = {
    clientes: {
        endpoint: 'php/clientes.php',
        campos: ['nome', 'cpf', 'nascimento', 'telefone', 'email', 'observacoes'],
        colunas: [
            r => escapar(r.nome),
            r => formatarCpf(r.cpf),
            r => formatarTelefone(r.telefone),
            r => escapar(r.email),
            r => `${r.idade} anos`
        ]
    },
    barbeiros: {
        endpoint: 'php/barbeiros.php',
        campos: ['nome', 'cpf', 'telefone', 'email', 'especialidade', 'comissao', 'ativo'],
        colunas: [
            r => escapar(r.nome),
            r => escapar(r.especialidade),
            r => `${formatarNumero(r.comissao)}%`,
            r => statusHtml(Number(r.ativo) === 1 ? 'Ativo' : 'Inativo')
        ]
    },
    servicos: {
        endpoint: 'php/servicos.php',
        campos: ['nome', 'categoria', 'duracao', 'preco', 'nivel', 'descricao', 'ativo'],
        colunas: [
            r => escapar(r.nome),
            r => escapar(r.categoria),
            r => `${r.duracao} min`,
            r => formatarMoeda(r.preco),
            r => escapar(r.nivel),
            r => statusHtml(Number(r.ativo) === 1 ? 'Ativo' : 'Inativo')
        ]
    },
    produtos: {
        endpoint: 'php/produtos.php',
        campos: ['nome', 'categoria', 'marca', 'estoque', 'preco', 'fornecedor', 'ativo'],
        colunas: [
            r => escapar(r.nome),
            r => escapar(r.categoria),
            r => String(r.estoque),
            r => formatarMoeda(r.preco),
            r => formatarMoeda(Number(r.preco) * Number(r.estoque)),
            r => statusHtml(Number(r.ativo) === 1 ? 'Ativo' : 'Inativo')
        ]
    },
    agendamentos: {
        endpoint: 'php/agendamentos.php',
        campos: ['cliente_id', 'barbeiro_id', 'servico_id', 'data', 'horario', 'confirmacao', 'status', 'observacoes'],
        colunas: [
            r => `${formatarData(r.data)} ${escapar(r.horario)}`,
            r => escapar(r.cliente_nome),
            r => escapar(r.barbeiro_nome),
            r => escapar(r.servico_nome),
            r => escapar(r.confirmacao),
            r => statusHtml(r.status)
        ]
    },
    usuarios: {
        endpoint: 'php/usuarios.php',
        campos: ['nome', 'email', 'perfil', 'ativo'],
        colunas: [
            r => escapar(r.nome),
            r => escapar(r.email),
            r => escapar(r.perfil),
            r => statusHtml(Number(r.ativo) === 1 ? 'Ativo' : 'Inativo'),
            r => escapar(r.criado_em)
        ]
    }
};

function escapar(valor) {
    return String(valor ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function somenteNumeros(valor) { return String(valor ?? '').replace(/\D/g, ''); }
function mascararCpf(valor) {
    const numeros = somenteNumeros(valor).slice(0, 11);
    return numeros.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}
function mascararTelefone(valor) {
    const numeros = somenteNumeros(valor).slice(0, 11);
    if (numeros.length <= 10) return numeros.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2');
    return numeros.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
}
function formatarCpf(valor) { return mascararCpf(valor); }
function formatarTelefone(valor) { return mascararTelefone(valor); }
function formatarMoeda(valor) { return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }
function formatarNumero(valor) { return Number(valor).toLocaleString('pt-BR', { maximumFractionDigits: 2 }); }
function formatarData(valor) {
    if (!valor) return '';
    const [ano, mes, dia] = valor.split('-');
    return `${dia}/${mes}/${ano}`;
}
function statusHtml(texto) {
    const classe = String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replaceAll(' ', '-');
    return `<span class="status ${classe}">${escapar(texto)}</span>`;
}

function mostrarMensagem(texto, tipo) {
    if (!mensagem) return;
    mensagem.textContent = texto;
    mensagem.className = `mensagem campo-largo ${tipo}`;
}

function configurarMascaras() {
    document.querySelectorAll('[data-mask="cpf"]').forEach((campo) => campo.addEventListener('input', () => campo.value = mascararCpf(campo.value)));
    document.querySelectorAll('[data-mask="telefone"]').forEach((campo) => campo.addEventListener('input', () => campo.value = mascararTelefone(campo.value)));
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

async function carregarOpcoes() {
    const selects = [...document.querySelectorAll('[data-options]')];
    await Promise.all(selects.map(async (select) => {
        const tipo = select.dataset.options;
        try {
            const retorno = await requisicao(`php/opcoes.php?tipo=${encodeURIComponent(tipo)}`);
            select.innerHTML = '<option value="">Selecione</option>' + retorno.dados.map(item => `<option value="${item.id}">${escapar(item.label)}</option>`).join('');
        } catch (erro) {
            select.innerHTML = '<option value="">Erro ao carregar</option>';
        }
    }));
}

async function carregarRegistros() {
    const config = configuracoes[modulo];
    if (!config) return;

    corpoTabela.innerHTML = '<tr><td class="vazio" colspan="10">Carregando...</td></tr>';
    try {
        const retorno = await requisicao(config.endpoint);
        registros = retorno.dados || [];
        renderizarTabela();
    } catch (erro) {
        corpoTabela.innerHTML = `<tr><td class="vazio" colspan="10">${escapar(erro.message)}</td></tr>`;
    }
}

function renderizarTabela() {
    const config = configuracoes[modulo];
    if (!registros.length) {
        corpoTabela.innerHTML = '<tr><td class="vazio" colspan="10">Nenhum registro encontrado.</td></tr>';
        return;
    }

    corpoTabela.innerHTML = registros.map((registro) => {
        const colunas = config.colunas.map(fn => `<td>${fn(registro)}</td>`).join('');
        return `<tr>${colunas}<td><div class="acoes-tabela"><button class="botao botao-secundario botao-pequeno" type="button" data-edit="${registro.id}">Editar</button><button class="botao botao-perigo botao-pequeno" type="button" data-delete="${registro.id}">Excluir</button></div></td></tr>`;
    }).join('');
}

function limparFormulario() {
    formulario.reset();
    formulario.querySelector('#id').value = '';
    const senha = formulario.querySelector('#senha');
    if (senha) senha.required = true;
    const botao = formulario.querySelector('[data-submit-label]');
    if (botao) botao.textContent = botao.textContent.replace('Atualizar', 'Salvar');
    configurarDataMinima();
    mostrarMensagem('', '');
}

function editar(id) {
    const registro = registros.find(r => Number(r.id) === Number(id));
    const config = configuracoes[modulo];
    if (!registro || !config) return;

    formulario.querySelector('#id').value = registro.id;
    config.campos.forEach((nome) => {
        const campo = formulario.elements[nome];
        if (campo && registro[nome] !== undefined && registro[nome] !== null) {
            campo.value = registro[nome];
            campo.dispatchEvent(new Event('input'));
        }
    });

    const senha = formulario.querySelector('#senha');
    if (senha) {
        senha.value = '';
        senha.required = false;
    }

    const botao = formulario.querySelector('[data-submit-label]');
    if (botao) botao.textContent = botao.textContent.replace('Salvar', 'Atualizar');
    mostrarMensagem('Registro carregado para edição.', 'info');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function excluir(id) {
    const config = configuracoes[modulo];
    if (!confirm('Deseja realmente excluir este registro?')) return;

    try {
        const retorno = await requisicao(`${config.endpoint}?id=${id}`, { method: 'DELETE' });
        mostrarMensagem(retorno.mensagem, 'sucesso');
        await carregarRegistros();
        if (modulo === 'agendamentos') await carregarOpcoes();
    } catch (erro) {
        mostrarMensagem(erro.message, 'erro');
    }
}

async function enviarFormulario(evento) {
    evento.preventDefault();
    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return;
    }

    const config = configuracoes[modulo];
    const botao = formulario.querySelector('button[type="submit"]');
    botao.disabled = true;
    mostrarMensagem('Salvando dados...', 'info');

    try {
        const retorno = await requisicao(config.endpoint, { method: 'POST', body: new FormData(formulario) });
        mostrarMensagem(retorno.mensagem, 'sucesso');
        formulario.reset();
        formulario.querySelector('#id').value = '';
        const senha = formulario.querySelector('#senha');
        if (senha) senha.required = true;
        const rotulo = formulario.querySelector('[data-submit-label]');
        if (rotulo) rotulo.textContent = rotulo.textContent.replace('Atualizar', 'Salvar');
        configurarDataMinima();
        if (modulo === 'agendamentos') await carregarOpcoes();
        await carregarRegistros();
    } catch (erro) {
        mostrarMensagem(erro.message, 'erro');
    } finally {
        botao.disabled = false;
    }
}

function eventosTabela(evento) {
    const editarBotao = evento.target.closest('[data-edit]');
    const excluirBotao = evento.target.closest('[data-delete]');
    if (editarBotao) editar(editarBotao.dataset.edit);
    if (excluirBotao) excluir(excluirBotao.dataset.delete);
}

async function iniciar() {
    if (!formulario || !configuracoes[modulo]) return;
    configurarMascaras();
    configurarDataMinima();
    await carregarOpcoes();
    await carregarRegistros();
    formulario.addEventListener('submit', enviarFormulario);
    corpoTabela.addEventListener('click', eventosTabela);
    document.querySelector('[data-cancel-edit]')?.addEventListener('click', limparFormulario);
    document.querySelector('[data-refresh]')?.addEventListener('click', carregarRegistros);
}

document.addEventListener('sessao-pronta', (evento) => {
    if (evento.detail) iniciar();
});
