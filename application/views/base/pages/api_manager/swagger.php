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

        #logout-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div id="auth-container">
        <input type="text" id="api-key" placeholder="Inserisci il tuo token API">
        <button onclick="authenticateAndLoadDocs()">Carica Documentazione</button>
        <p id="saved-key-message" style="display: none;">Hai un token API salvato. Clicca su "Carica Documentazione" per usarlo o inserisci un nuovo token.</p>
    </div>
    <div id="swagger-ui"></div>
    <button id="logout-button" style="display: none;" onclick="logout()">Logout</button>
    <script src="<?php echo base_url('assets/swagger/swagger-ui-bundle.js'); ?>"></script>
    <script src="<?php echo base_url('assets/swagger/swagger-ui-standalone-preset.js'); ?>"></script>
    <script>
        let savedApiKey = localStorage.getItem('apiKey');

        window.onload = function() {
            if (savedApiKey) {
                document.getElementById('saved-key-message').style.display = 'block';
            }
        }

        function authenticateAndLoadDocs() {
            const inputApiKey = document.getElementById('api-key').value;
            const apiKey = inputApiKey || savedApiKey;
            if (!apiKey) {
                alert('Per favore, inserisci un token API valido.');
                return;
            }

            if (inputApiKey) {
                localStorage.setItem('apiKey', inputApiKey);
                savedApiKey = inputApiKey;
            }

            loadSwaggerDocs(apiKey);
        }

        function loadSwaggerDocs(apiKey) {
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
                    // Aggiungi il token di autenticazione a tutte le operazioni nella spec
                    Object.values(spec.paths).forEach(path => {
                        Object.values(path).forEach(operation => {
                            if (!operation.security) {
                                operation.security = [{ BearerAuth: [] }];
                            }
                        });
                    });

                    // Aggiungi la definizione di sicurezza se non esiste
                    if (!spec.components) {
                        spec.components = {};
                    }
                    if (!spec.components.securitySchemes) {
                        spec.components.securitySchemes = {};
                    }
                    spec.components.securitySchemes.BearerAuth = {
                        type: 'http',
                        scheme: 'bearer',
                        bearerFormat: 'JWT'
                    };

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
                            if (request.method === 'GET' || request.method === 'HEAD') {
                                delete request.body;
                            }
                            return request;
                        }
                    });

                    // Nascondi il bottone "Authorize"
                    const authButtonInterval = setInterval(() => {
                        const authButton = document.querySelector('.authorize');
                        if (authButton) {
                            authButton.style.display = 'none';
                            clearInterval(authButtonInterval);
                        }
                    }, 100);

                    document.getElementById('swagger-ui').style.display = 'block';
                    document.getElementById('auth-container').style.display = 'none';
                    document.getElementById('logout-button').style.display = 'block';
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Si è verificato un errore durante il caricamento della documentazione. Verifica il tuo token API e riprova.');
                });
        }

        function logout() {
            localStorage.removeItem('apiKey');
            savedApiKey = null;
            location.reload();
        }
    </script>
</body>

</html>