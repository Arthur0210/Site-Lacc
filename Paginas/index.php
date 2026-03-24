<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>LACC - Laboratório de Química Computacional Aplicada</title>
</head>
<body>
    
    <?php include 'header.html'; ?>

    <section class="hero-section">
        <div class="hero-content">
            <h1>Laboratório de Química Computacional Aplicada</h1>
            <p class="subtitle">Explorando as fronteiras da ciência através da simulação molecular.</p>
        </div>
    </section>

    <section class="cta-section">
        <div class="cta-content">
            <h2>Já faz parte da nossa comunidade?</h2>
            <p>Cadastre-se para acompanhar as últimas publicações e interagir com nossos pesquisadores.</p>
            <a href="../Login/cadastrar.php" class="btn">Criar minha conta</a>
        </div>
    </section>

    <main>
        <section id="sobre" class="about-section">
            <h2 class="section-title">Sobre o LACC</h2>
            <div class="about-content">
                <p>O Laboratório de Química Computacional Aplicada (LACC) é um centro de pesquisa de ponta dedicado ao estudo de sistemas químicos e biológicos complexos utilizando métodos computacionais avançados. Nossa missão é desenvolver e aplicar novas teorias e algoritmos para resolver problemas fundamentais em química, física e biologia.</p>
                <p>Nossas linhas de pesquisa abrangem desde o planejamento de novos fármacos e materiais até a investigação de mecanismos de reações catalíticas. Contamos com uma infraestrutura computacional de alto desempenho e uma equipe de pesquisadores altamente qualificada e apaixonada pela ciência.</p>
            </div>
        </section>

        <section id="equipe" class="team-section">
            <h2 class="section-title">Nossa Equipe</h2>
            <div class="team-container">

                <div class="team-member">
                    <img src="../Imagens/placeholder-membro.png" alt="Foto do Coordenador">
                    <div class="member-info">
                        <h3>Dr. Nome do Coordenador</h3>
                        <span>Coordenador do LACC</span>
                        <p>Especialista em dinâmica molecular e planejamento de fármacos.</p>
                    </div>
                </div>

                <div class="team-member">
                    <img src="../Imagens/placeholder-membro.png" alt="Foto do Pesquisador">
                    <div class="member-info">
                        <h3>Dra. Nome da Pesquisadora</h3>
                        <span>Pesquisadora Sênior</span>
                        <p>Foco em reações catalíticas e química quântica.</p>
                    </div>
                </div>

                <div class="team-member">
                    <img src="../Imagens/placeholder-membro.png" alt="Foto do Estudante">
                    <div class="member-info">
                        <h3>Nome do Aluno de Doutorado</h3>
                        <span>Doutorando</span>
                        <p>Desenvolve novos métodos para análise de dados de simulações.</p>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <?php include 'footer.html'; ?>
    
</body>
</html>