<!DOCTYPE HTML>
<html>
    <head>
        <title>HELP API MasterCRM</title>
        <style>
            table > thead > tr > th:first-child, table > tbody > tr > td:first-child { text-align: center; }
            table > thead > tr > th:nth-child(2) { text-align: left; }
            table th, table td { padding: 5px; }
            ul li { margin-bottom: 20px }
        </style>
    </head>
    <body>
        <h1>HELP API</h1>

        <h2>Introduzione</h2>
        <p>
            In questa guida troverete tutte le informazioni necessarie all'uso delle API REST. Il sistema &egrave; basato su scambio dati in GET/POST. I dati di 
            ritorno sono sempre in formato JSON e formattati secondo il seguente standard:<br />
            <pre>
                {
                    "status": 0,
                    "message": "Messaggio di ritorno",
                    "data": {
                        "campo1": {
                            "campo_figlio":"valore1"
                        },
                        "campo2":"valore2"
                    }
                }
            </pre>
            <h3>Legenda</h3>
            <p>
                <strong>status</strong>: esito della chiamata (0 = success, 1 = error)<br />
                <strong>message</strong>: eventuale messaggio di risposta. Questo attributo viene principalmente utilizzato per stampare l'errore verificatosi<br />
                <strong>data</strong>: eventuali dati richiesti (soprattutto per le ricerce e per il ritorno di operazioni di insert/update)<br />
            </p>
            
        </p>
        <h2>Informazioni generali</h2>
            <p>
                Tutto il sistema &egrave; basato su entit&agrave; (ovvero le tabelle del database) e i fields (ovvero i campi di ciascuna tabella), ognuno con le sue propriet&agrave;.<br />
                Per i tipi di dato (varchar, boolean, int), si prega di fare riferimento alla guida ufficiale di <a href="https://www.postgresql.org/docs/9.6/static/datatype.html">PostgreSQL</a> o alla tabella riportata <a href="#">qui</a>.<br />
            </p>
        <h2>Autenticazione</h2>
        <p>
            Le api sono sempre e comunque protette, anche solo per accedere a questa guida. Per utilizzarle dovete essere in possesso di due chiavi, una pubblica e una privata. <br />
            La chiave pubblica deve essere sempre passata col parametro (GET) <strong>token</strong>.<br />
            La chiave privata invece vi servir&agrave; per <a href="#">generare un codice</a> di controllo <strong>crc</strong> che permette al sistema di autorizzare (o negare) la richiesta.<br />
            <br />
            Ci sono altri due livelli di protezione:<br />
            <ul>
                <li>
                    <i>ms tra una richiesta e un'altra</i>: troppe connessioni verranno rifiutate se non trascorso del tempo dalla precedente (generalmente 1 secondo)
                </li>
                <li>
                    <i>richieste al minuto</i>: pur rispettando la regola di cui sopra, in alcuni casi il sistema potrebbe bloccare troppe richieste al minuto (generalmente 20)
                </li>
                <li>
                    <i>permessi ad hoc</i>: alcune entit&agrave; e/o campi di queste, possono avere accessi limitati. Alcuni campi quindi potranno essere totalmente invisibili e inaccessibili, 
                    altri saranno in sola lettura, altri ancora accessibili in lettura e scrittura. E' possibile conoscere tutti questi dettagli, cliccando questo link: 
                    <a href="<?php echo base_url("rest/v1/entities?token={token}&crc={crc}"); ?>"><?php echo base_url("rest/v1/entities?token={token}&crc={crc}"); ?></a>
                </li>
            </ul>
            Questi sono parametri personalizzati in base alle esigenze del cliente.
            <h3>
                Codice crc
            </h3>
            <p>
                Il codice di controllo crc serve a evitare chiamate non autorizzate. Solo chi &egrave; in possesso della chiave privata potr&agrave; infatti generare questa stringa 
                di controllo. La generazione dipender&agrave; infatti da questi parametri:
                <ul>
                    <li>Chiave privata</li>
                    <li>Metodo chiamato</li>
                    <li>Json dei parametri POST</li>
                </ul>
                Per calcolarla &egrave; necessario concatenare i seguenti valori e calcolarne l'md5:<br />
                <br />
                {private_key}{call}{json(post)}<br />
                <br />
                esempio (codice PHP):<br />
                <br />
                <code>
                $crc = md5($private_key.$call.json_encode($post_data));
                </code>
                <br />
                <br />
                <i>nb.: se la chiamata non richiede dati in post o non ne ha, passare un array contenente un solo elemento false in formato json (ovvero, string '[false]')</i>
            </p>
        </p>
        <h2>Chiamate valide</h2>
        <ul>
            <li>Finestra di aiuto <pre>(GET)   /rest/v1/help?token={public_token}&amp;crc={crc}</pre></li>
            <li>Ottenere informazioni sulle entit&agrave; <pre>(GET)   /rest/v1/entities?token={public_token}&amp;crc={crc}</pre></li>
            <li>Ottenere tutti i record di un'entit&agrave; <pre>(GET)   /rest/v1/index/{entity}</pre></li>
            <li>Ottenere un record per id <pre>(GET)   /rest/v1/view/{entity}/{id}</pre></li>
            <li>Inserire nuovo record <pre>(POST)  /rest/v1/create/{entity}</pre></li>
            <li>Update di un record <pre>(POST)  /rest/v1/edit/{entity}/{id}</pre></li>
            <li>Delete di un record <pre>(GET)   /rest/v1/delete/{entity}/{id}</pre></li>
            <li>
                Ricerca record: <pre>(POST)  /rest/v1/search/{entity}</pre>
                La ricerca prevede i seguenti parametri facoltativi (in POST):<br />
                <ul>
                    <li>limit: definisce quanti record estrarre. Se non passato, restituisce tutti i record</li>
                    <li>offset: definisce da quale n-esimo record partire. Se non passato, default: 0.</li>
                    <li>orderby: ordinamento dei risultati. Se non passato, ordininamento per oid (<a href="https://www.postgresql.org/docs/9.1/static/datatype-oid.html">https://www.postgresql.org/docs/9.1/static/datatype-oid.html</a>)</li>
                    <li>orderdir: ordinamento ascendente o discendente. Se non passato, default: ASC.</li>
                </ul><br />
                E' possibile anche effettuare delle ricerche basate su chiave=>valore inserendo nell'array post una chiave "filter", contenente a sua volta il json dei filtri.
                Questi filtri devono essere inseriti con il nome del campo da ricercare come chiave e come valore il valore che deve avere quel campo.<br/>
                &Egrave; possibile avere un array di valori. Ad esempio se la chiave del post &egrave; id e passo pi&ugrave; valori come array allora vengono recuperati tutti i record aventi id IN (valori passati).<br/>
                &Egrave; possibile avere più di un campo in order by, basta separare con due punti ':' i campi in order_by e order_dir
            </li>
        </ul>
        
        
        <h2>Tipi di ritorno</h2>
        <p>Il tipo di ritorno di default &egrave; un <strong>json</strong> nella forma</p>
        <pre>
    - status: int
    - message: string
    - data: array
        </pre>

        <p>In caso di successo status avr&agrave; valore <strong>0</strong> e data conterr&agrave; i dati richiesti, mentre in caso di errore status conterr&agrave; il codice di errore e message una rappresentazione testuale dell'errore.</p>
        <table>
            <thead>
                <tr>
                    <th>Stato</th>
                    <th>Descrizione</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>0</td>
                    <td>Nessun errore registrato, dati inviati in message</td>
                </tr>
                <?php foreach ($errors as $status => $message): ?>
                    <tr>
                        <td><?php echo $status; ?></td>
                        <td><?php echo htmlentities($message); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        
        <p>
            Alternativamente &egrave; possibile chiedere che le api facciano un
            <strong>redirect</strong> ad un'altra destinazione dopo un'operazione
            di <strong>inserimento</strong>, <strong>modifica</strong> o 
            <strong>cancellazione</strong> passando come ultimo parametro la
            stringa <i>redirect</i>.<br/>Inoltre &egrave; necessario passare come parametro
            in $_GET l'url di destinazione alla chiave <i>url</i>.
        </p>
        <ul>
            <li>
                creazione:
                <pre>/rest/v1/create/:entity/redirect?url=:url</pre>
            </li>
                
            <li>
                modifica:
                <pre>/rest/v1/edit/:entity/:id/redirect?url=:url</pre>
            </li>
                
            <li>
                cancellazione:
                <pre>/rest/v1/delete/:entity/:id/redirect?url=:url</pre>
            </li>
        </ul>
        <h2>Esempi</h2>
        <p>A <a href="<?php echo base_url('esempio_rest_api.zip'); ?>">questo link</a> &egrave; possibile scaricare degli esempi di chiamate PHP da utilizzare come tracciato per la programmazione.</p>
    </body>
</html>
