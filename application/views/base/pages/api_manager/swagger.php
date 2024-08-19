<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>API Documentation</title>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/swagger/swagger-ui.css'); ?>" />
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="<?php echo base_url('assets/swagger/swagger-ui-bundle.js'); ?>"></script>
    <script src="<?php echo base_url('assets/swagger/swagger-ui-standalone-preset.js'); ?>"></script>
    <script>
        window.onload = function () {
            window.ui = SwaggerUIBundle({
                url: "<?php echo site_url('rest/v1/generateSwaggerDocumentation'); ?>",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
            });
        };
    </script>
</body>

</html>