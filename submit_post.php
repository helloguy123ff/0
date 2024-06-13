<?php
// Verificar se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se os campos necessários foram preenchidos
    if (isset($_POST["content"]) && isset($_FILES["image"])) {
        // Conectar ao banco de dados (substitua os valores conforme necessário)
        $servername = "localhost";
        $username = "seu_usuario";
        $password = "sua_senha";
        $dbname = "seu_banco_de_dados";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificar a conexão
        if ($conn->connect_error) {
            die("Erro de conexão com o banco de dados: " . $conn->connect_error);
        }

        // Obter os dados do formulário
        $content = $_POST["content"];
        $categoryAbbreviation = $_POST["category_abbreviation"];

        // Verificar se um nome de autor foi fornecido
        if (isset($_POST["author"]) && !empty($_POST["author"])) {
            $author = $_POST["author"];
        } else {
            // Definir o autor como "Anônimo" se nenhum nome foi fornecido
            $author = "Anônimo";
        }

        // Upload da imagem
        $targetDir = "uploads/"; // Diretório onde as imagens serão armazenadas
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Verificar se o arquivo é uma imagem real
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check !== false) {
                echo "O arquivo é uma imagem - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                echo "O arquivo não é uma imagem.";
                $uploadOk = 0;
            }
        }

        // Verificar se o arquivo já existe
        if (file_exists($targetFile)) {
            echo "Desculpe, o arquivo já existe.";
            $uploadOk = 0;
        }

        // Verificar o tamanho do arquivo (limite de 5 MB)
        if ($_FILES["image"]["size"] > 5000000) {
            echo "Desculpe, o arquivo é muito grande.";
            $uploadOk = 0;
        }

        // Permitir apenas alguns formatos de arquivo
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
            echo "Desculpe, apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
            $uploadOk = 0;
        }

        // Verificar se $uploadOk é 0 por algum erro
        if ($uploadOk == 0) {
            echo "Desculpe, seu arquivo não foi enviado.";
        // Se tudo estiver ok, tentar fazer o upload do arquivo
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                echo "O arquivo ". basename( $_FILES["image"]["name"]). " foi enviado com sucesso.";
            } else {
                echo "Desculpe, houve um erro ao enviar o seu arquivo.";
            }
        }

        // Preparar e executar a declaração SQL para inserir o post no banco de dados
        $stmt = $conn->prepare("INSERT INTO posts (author, content, image_path, category_abbreviation) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $author, $content, $targetFile, $categoryAbbreviation);

        if ($stmt->execute() === TRUE) {
            echo "Post criado com sucesso.";
        } else {
            echo "Erro ao criar o post: " . $stmt->error;
        }

        // Fechar a conexão com o banco de dados
        $stmt->close();
        $conn->close();
    } else {
        echo "Por favor, preencha todos os campos obrigatórios.";
    }
} else {
    echo "Erro: o formulário não foi submetido corretamente.";
}
?>
