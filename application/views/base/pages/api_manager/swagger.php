<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>API Documentation</title>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/swagger/swagger-ui.css'); ?>" />
    <style>
        #auth-container {
            max-width: 300px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        #auth-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }

        #auth-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #swagger-ui {
            display: none;
        }
    </style>
</head>

<body>
    <div id="auth-container">
        <input type="text" id="api-key" placeholder="Inserisci il tuo token API">
        <button onclick="authenticateAndLoadDocs()">Carica Documentazione</button>
    </div>
    <div id="swagger-ui"></div>
    <script src="<?php echo base_url('assets/swagger/swagger-ui-bundle.js'); ?>"></script>
    <script src="<?php echo base_url('assets/swagger/swagger-ui-standalone-preset.js'); ?>"></script>
    <script>
        function authenticateAndLoadDocs() {
            const apiKey = document.getElementById('api-key').value;
            if (!apiKey) {
                alert('Per favore, inserisci un token API valido.');
                return;
            }

            // Effettua una richiesta per ottenere la documentazione Swagger
            fetch("<?php echo site_url('rest/v1/generateSwaggerDocumentation'); ?>", {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + apiKey
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Autenticazione fallita o errore nel recupero della documentazione');
                    }
                    return response.json();
                })
                .then(spec => {
                    // Inizializza Swagger UI con la spec ricevuta
                    window.ui = SwaggerUIBundle({
                        spec: spec,
                        dom_id: '#swagger-ui',
                        deepLinking: true,
                        presets: [
                            SwaggerUIBundle.presets.apis,
                            SwaggerUIStandalonePreset
                        ],
                        plugins: [
                            SwaggerUIBundle.plugins.DownloadUrl
                        ],
                        requestInterceptor: (request) => {
                            request.headers['Authorization'] = 'Bearer ' + apiKey;
                            // Rimuovi il body per le richieste GET e HEAD
                            if (request.method === 'GET' || request.method === 'HEAD') {
                                delete request.body;
                            }
                            return request;
                        }
                    });

                    // Mostra la documentazione Swagger
                    document.getElementById('swagger-ui').style.display = 'block';
                    document.getElementById('auth-container').style.display = 'none';
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Si è verificato un errore durante il caricamento della documentazione. Verifica il tuo token API e riprova.');
                });
        }
    </script>
</body>

</html>