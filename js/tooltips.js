// Fecha todos os tooltips abertos
function fecharTodos() {
    document.querySelectorAll('.btn-info.ativo').forEach(function(btn) {
        btn.classList.remove('ativo');
    });
}

// Alterna o tooltip do botão clicado (usado no mobile, onde não há hover)
function alternarTooltip(botao) {
    const estaAberto = botao.classList.contains('ativo');
    fecharTodos();            // Fecha qualquer outro aberto
    if (!estaAberto) {
        botao.classList.add('ativo'); // Abre o clicado
    }
}

// Clicou fora de qualquer botão de info: fecha todos
document.addEventListener('click', function(e) {
    if (!e.target.closest('.btn-info')) {
        fecharTodos();
    }
});