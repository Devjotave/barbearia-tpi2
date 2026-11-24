async function carregarDashboard() {
    try {
        const retorno = await requisicao('php/dashboard.php');
        Object.entries(retorno.dados || {}).forEach(([chave, valor]) => {
            const elemento = document.querySelector(`[data-count="${chave}"]`);
            if (elemento) elemento.textContent = valor;
        });
    } catch (erro) {
        document.querySelectorAll('[data-count]').forEach((elemento) => elemento.textContent = '!');
    }
}

document.addEventListener('sessao-pronta', (evento) => {
    if (evento.detail) carregarDashboard();
});
