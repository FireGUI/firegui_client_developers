<?php

log_message('debug', 'Started migration 4.1.4...');


// Apriamo il file in lettura/scrittura
$filename = '.htaccess';
$file = fopen($filename, 'r+');

// Verifichiamo se il file esiste e se è stato aperto correttamente
if ($file === false) {
  log_message('error', 'Impossibile aprire il file htaccess.');
} else {

    // Lettura del contenuto del file
    $fileContent = fread($file, filesize($filename));

    // Cerchiamo la stringa di ricerca all'interno del contenuto del file
    $searchString = 'RewriteRule ^template_bridge/(.*)$ templatebridge.php?/$1 [L]';
    $position = strpos($fileContent, $searchString);

    // Verifichiamo se la stringa di ricerca è stata trovata all'interno del file
    if ($position === false) {
        log_message('error', 'Impossibile trovare la stringa di ricerca all\'interno del file htaccess.');
    } else {

        // Aggiungiamo la nuova riga di testo dopo la stringa di ricerca trovata
        $insertString = "\n\tRewriteRule ^public/([^/]+)/(.+)$ application/modules/$1/assets/$2 [L]";

        if(strpos($fileContent, $insertString) === false) {
            $newContent = substr_replace($fileContent, $insertString, $position + strlen($searchString), 0);

            // Spostiamo il cursore alla fine del file e scriviamo il nuovo contenuto
            fseek($file, 0);
            fwrite($file, $newContent);

            // Chiudiamo il file
            fclose($file);

            log_message('debug', 'File htaccess modificato');
        } else {
            log_message('debug', 'Riga già presente nel file htaccess');
        }
    }
}
log_message('debug', 'Finished migration 4.1.4...');


