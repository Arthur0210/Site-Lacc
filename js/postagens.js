document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const btnAbrir = document.getElementById('btn-nova-postagem');
    const btnFechar = document.getElementById('modal-close');
    const btnPublicar = document.getElementById('btn-publicar');
    const formMsg = document.getElementById('form-msg');
    const tituloInput = document.getElementById('titulo');
    const posterInput = document.getElementById('poster');

    let conteudoHtml = '';

    if (btnAbrir) {
        // Inicializa o Pell sem atribuir a uma variável, já que não a usaremos depois
        pell.init({
            element: document.getElementById('editor-pell'),
            onChange: html => { conteudoHtml = html; },
            defaultParagraphSeparator: 'p',
            actions: [
                'bold', 'italic', 'underline', 'strikethrough',
                'olist', 'ulist', 'link', 'heading1', 'heading2'
            ]
        });

        btnAbrir.onclick = () => {
            formMsg.textContent = '';
            tituloInput.value = '';
            posterInput.value = '';
            const internalEditor = document.querySelector('.pell-content');
            if (internalEditor) internalEditor.innerHTML = '';
            conteudoHtml = '';
            modal.style.display = 'block';
        };

        btnFechar.onclick = () => { modal.style.display = 'none'; };

        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };

        btnPublicar.onclick = async () => {
            const titulo = tituloInput.value.trim();
            const posterFile = posterInput.files[0];

            if (!titulo || !conteudoHtml || conteudoHtml === '<p><br></p>') {
                formMsg.textContent = "Título e conteúdo são obrigatórios.";
                formMsg.style.color = 'red';
                return;
            }

            const formData = new FormData();
            formData.append('titulo', titulo);
            formData.append('conteudo', conteudoHtml);
            if (posterFile) formData.append('poster', posterFile);

            formMsg.textContent = 'Publicando...';
            formMsg.style.color = 'black';

            try {
                const response = await fetch('criar_postagem.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    // "data.msg" deve existir no JSON retornado pelo seu PHP
                    formMsg.textContent = data.msg || data.message || "Erro ao publicar.";
                    formMsg.style.color = 'red';
                }
            } catch (error) {
                formMsg.textContent = 'Erro de conexão. Tente novamente.';
                formMsg.style.color = 'red';
            }
        };
    }
});