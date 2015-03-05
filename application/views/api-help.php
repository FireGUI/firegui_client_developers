<!DOCTYPE HTML>
<html>
    <head>
        <title>HELP API MasterCRM</title>
        <style>
            table > thead > tr > th:first-child, table > tbody > tr > td:first-child {
                text-align: center;
            }
            
            table > thead > tr > th:nth-child(2) {
                text-align: left;
            }
            
            table th, table td {
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <h1>HELP API</h1>


        <h2>Chiamate valide</h2>
        <ul>
            <li>Ottenere informazioni sulla struttura di un'entit&agrave; <pre>(GET)   /api/describe/{entity}</pre></li>
            <li>Ottenere informazioni sulle tabelle di supporto <pre>(GET)   /api/support_list</pre></li>
            <li>Finestra di aiuto <pre>(GET)   /api/help</pre></li>
            <li>Ottenere tutti i record di un'entit&agrave; <pre>(GET)   /api/index/:entity</pre></li>
            <li>Ottenere un record per id <pre>(GET)   /api/view/:entity/:id</pre></li>
            <li>
                Ricerca record: per cercare i record &egrave; sufficiente passare in get o in post il nome del campo da ricercare come chiave e come valore il valore che deve avere quel campo.<br/>
                Se passato in post, &egrave; possibile avere un array di valori. Ad esempio se la chiave del post &egrave; id e passo pi&ugrave; valori come array allora vengono recuperati tutti i record aventi id IN (valori passati).<br/>
                &Egrave; possibile avere più di un campo in order by, basta separare con due punti ':' i campi in order_by e order_dir nel seguente modo: campo_1:campo_2/dir_1:dir_2
                <pre>(ANY)  /api/search/:entity/:limit/:offset/:order_by/:order_dir</pre>
            </li>
            <li>Inserire nuovo record <pre>(POST)  /api/create/:entity</pre></li>
            <li>Update di un record <pre>(POST)  /api/edit/:entity/:id</pre></li>
            <li>Delete di un record <pre>(GET)   /api/delete/:entity/:id</pre></li>
                
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
                <pre>/api/create/:entity/redirect?url=:url</pre>
            </li>
                
            <li>
                modifica:
                <pre>/api/edit/:entity/:id/redirect?url=:url</pre>
            </li>
                
            <li>
                cancellazione:
                <pre>/api/delete/:entity/:id/redirect?url=:url</pre>
            </li>
        </ul>
        
        
    </body>
</html>
