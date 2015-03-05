<?php

class Cron extends MX_Controller {

    var $template = array();
    var $settings = null;
    var $client = null;
    var $service = null;

    public function __construct() {
        parent::__construct();
        
        /*if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Modulo non installato');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Accesso vietato');
        }*/

        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();

        require realpath(dirname(__FILE__)) . '/../google-api-calendar/autoload.php';

        $this->client = new Google_Client();
        // OAuth2 client ID and secret can be found in the Google Developers Console.
        $this->client->setClientId(GOOGLEAPP_CLIENTID);
        $this->client->setClientSecret(GOOGLEAPP_SECRET);
        $this->client->setRedirectUri(base_url() . 'google_calendar/oauth2callback');
        $this->client->setAccessType('offline');
        $this->client->addScope('https://www.googleapis.com/auth/calendar');

        $this->service = new Google_Service_Calendar($this->client);
        
        $this->load->helper('date');
    }

    public function sincronizza() {
        $this->output->enable_profiler(true);

        //Prendo tutti gli utenti con sincronizzazione attiva
        $sincronizzazioni = $this->db->get_where('google_calendar LEFT JOIN ' . LOGIN_ENTITY . ' ON (google_calendar.google_calendar_utente = ' . LOGIN_ENTITY . '.' . LOGIN_ENTITY . '_id)', "google_calendar_token IS NOT NULL AND google_calendar_token <> ''"
                )->result_array();

        $fuso = '01:00'; //'02:00';

        foreach ($sincronizzazioni as $sincronizzazione) {
            //Se ho già fatto una sincronizzazione, è inutile che riprendo tutti gli appuntamenti, ma prendo solo l'ultimo mese
            if ($sincronizzazione['google_calendar_last_sync']) {
                $data_da = date3339(mktime() - 30 * 3600 * 24) . $fuso;
            } else {
                $data_da = '';
            }

            $google_ids = array();
            $aggiornati_crm = 0;
            $aggiornati_google = 0;

            $inseriti_crm = 0;
            $inseriti_google = 0;

            $eliminati_crm = 0;
            $eliminati_google = 0;

            if (!$sincronizzazione['google_calendar_auth_code']) {
                debug("Auth code mancante... possibile?", true);
                continue;
            }
            $this->client->setAccessToken($sincronizzazione['google_calendar_token']);
            if ($this->client->isAccessTokenExpired()) {
                debug('token per ' . $sincronizzazione['google_calendar_utente'] . ' scaduto.');
                debug($sincronizzazione['google_calendar_token']);
                if ($this->client->getRefreshToken()) {
                    $this->client->refreshToken($this->client->getRefreshToken());
                    if ($this->client->isAccessTokenExpired()) {
                        debug("Token scaduto ancora!...");
                        continue;
                    } else {
                        //debug("Token refreshato!!!",true);
                    }
                } else {
                    debug("Anomalia agente {$sincronizzazione['google_calendar_utente']}: manca il refresh token nel json");
                    continue;
                }
            }

            $calendarList = $this->service->calendarList->listCalendarList();
            $calendar_to_sync = null;
            while (true) {
                foreach ($calendarList->getItems() as $calendarListEntry) {
                    //Verifico che sia il calendario da sincronizzare
                    if ($calendarListEntry->getSummary() != $sincronizzazione['google_calendar_calendario']) {
                        continue;
                    }
                    $calendar_to_sync = $calendarListEntry;
                }
                $pageToken = $calendarList->getNextPageToken();
                if ($pageToken) {
                    $optParams = array('pageToken' => $pageToken);
                    $calendarList = $this->service->calendarList->listCalendarList($optParams);
                } else {
                    break;
                }
            }
            //Prendo gli eventi da google...
            if ($calendar_to_sync == null) {
                //Passo al prossimo agente da sincronizzare, visto che questo non ha alcun calendario configurato
                debug("Calendario non trovato");
                continue;
            }


            $options = array(
                'maxResults' => 500,
                'orderBy' => 'startTime',
                //Questo parametro l'ho aggiunto io, non esiste documentazione. Ho modificato anche calendar.php per permettere l'uso di questo parametro... in caso rimuovere tutto!
                'sortOrder' => 'descending',
                
                'singleEvents' => true,
            );
            if ($data_da) {
                $options['timeMin'] = $data_da;
            }
            $events = $this->service->events->listEvents($calendar_to_sync->id, $options);
            //$events->set('sortorder', 'descending');
            while (true) {
                foreach ($events->getItems() as $event) {
                    usleep(50000);
                    echo('.');
                    flush();
                    ob_flush();

                    if ($event->getRecurrence()) {
                        continue;
                    }
                    //debug('DENTRO 1');
                    $appuntamento_arr = array(
                        GC_FIELD_SORGENTE => 'google',
                        GC_FIELD_UTENTE => $sincronizzazione['google_calendar_utente'],
                        GC_FIELD_DATA_DA => ($event->getStart()->dateTime) ? $event->getStart()->dateTime : $event->getStart()->date,
                        GC_FIELD_DATA_A => ($event->getEnd()->dateTime) ? $event->getEnd()->dateTime : $event->getEnd()->date,
                        GC_FIELD_GIORNATA_INTERA => ($event->getStart()->dateTime) ? 'f' : 't',
                        GC_FIELD_TITOLO => substr($event->getSummary(), 0, 250),
                        GC_FIELD_DESCRIZIONE => $event->getDescription(),
                        GC_FIELD_LUOGO => $event->getLocation(),
                        GC_FIELD_CODICE_ESTERNO => $event->getId(),
                        GC_FIELD_DATA_MODIFICA => $event->getUpdated(),
                    );
                    //debug('DENTRO 2');
                    
                    $appuntamenti_obj = $this->db->where(GC_FIELD_CODICE_ESTERNO, $event->getId())->where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->limit(1)->get(GC_ENTITY);
                    if ($appuntamenti_obj->num_rows() == 1) {
                        
                        $appuntamento_array = $appuntamenti_obj->row_array();

                        if ($appuntamento_array[GC_FIELD_CANCELLATO] == 't') {
                            debug('Cancello evento da google');
                            $this->service->events->delete($calendar_to_sync->getId(), $event->getId());
                            
                            $eliminati_google++;
                        } else {
                            if (str_pad(substr(str_replace(' ', 'T', $appuntamento_array[GC_FIELD_DATA_MODIFICA]), 0, 23), 23, '0', STR_PAD_RIGHT) . "Z" < $event->getUpdated()) {
                                //debug('Aggiorno appuntamento su crm.', true);
                                $this->db->where(GC_FIELD_CODICE_ESTERNO, $event->getId())->where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->update(GC_ENTITY, $appuntamento_arr);
                                $aggiornati_crm++;
                            } elseif (str_pad(substr(str_replace(' ', 'T', $appuntamento_array[GC_FIELD_DATA_MODIFICA]), 0, 23), 23, '0', STR_PAD_RIGHT) . "Z" > $event->getUpdated()) {
                                //debug('Aggiorno appuntamento su google.', true);
                                $event->setSummary($appuntamento_array[GC_FIELD_TITOLO]);
                                $event->setDescription($appuntamento_array[GC_FIELD_DESCRIZIONE]);
                                //$event->setUpdated(str_replace(' ', 'T', $appuntamento_array[GC_FIELD_DATA_MODIFICA]). ".000");
                                $event->setLocation($appuntamento_array[GC_FIELD_LUOGO]);
                                $start = new Google_Service_Calendar_EventDateTime();
                                $start->setDateTime(str_replace(' ', 'T', $appuntamento_array[GC_FIELD_DATA_DA]) . ".000+$fuso");
                                $event->setStart($start);

                                $event->setSequence($event->getSequence() + 1);

                                $end = new Google_Service_Calendar_EventDateTime();
                                $end->setDateTime(str_replace(' ', 'T', $appuntamento_array[GC_FIELD_DATA_A]) . ".000+$fuso");
                                $event->setEnd($end);
                                
                                $updatedEvent = $this->service->events->update($calendar_to_sync->getId(), $event->getId(), $event);
                                
                                if ($updatedEvent->getUpdated()) {
                                    /* debug($event->getUpdated());
                                      debug($updatedEvent->getUpdated());
                                      debug(str_pad(substr(str_replace(' ', 'T', $appuntamento_array[GC_FIELD_DATA_MODIFICA]),0,23),23,'0',STR_PAD_RIGHT) . "Z");

                                      debug("Sincronizzo i due orari di modifica",true); */
                                    $this->db->where(GC_ENTITY.'_id', $appuntamento_array[GC_ENTITY.'_id'])->where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->update(GC_ENTITY, array(GC_FIELD_DATA_MODIFICA => $updatedEvent->getUpdated()));
                                } else {
                                    debug($event->getUpdated());
                                    debug($updatedEvent->getUpdated());
                                    debug(str_pad(substr(str_replace(' ', 'T', $appuntamento_array[GC_FIELD_DATA_MODIFICA]), 0, 23), 23, '0', STR_PAD_RIGHT) . "Z");

                                    debug('Appuntamento anomalo senza data di modifica2');
                                    debug($event, true);
                                    //$g = new GCal();
                                    //$g->login($sincronizzazione['sincronizzazione_user'], decript($sincronizzazione['sincronizzazione_password']));
                                }
                                $aggiornati_google++;
                            }
                        }
                    } else {
                        //debug('Inserisco appuntamento su crm',true);
                        $this->db->insert(GC_ENTITY, $appuntamento_arr);
                        $inseriti_crm++;
                    }
                    $google_ids[] = $event->getId();
                }
                $pageToken = $events->getNextPageToken();
                if ($pageToken) {
                    $optParams = array('pageToken' => $pageToken);
                    $events = $this->service->events->listEvents('primary', $optParams);
                } else {
                    break;
                }
            }
            //Cancello gli appuntamenti che non ho più nel calendario google (cancellati quindi)

            if ($google_ids !== array()) {
                if ($data_da != '') {
                    //debug('Cancellare evento da crm 1',true);
                    $res = $this->db->
                            where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->
                            where(GC_FIELD_CANCELLATO, 'f')->
                            where_not_in(GC_FIELD_CODICE_ESTERNO, $google_ids)->
                            where(GC_FIELD_DATA_MODIFICA.' >= (now() - interval \'30 days\')')->
                            where(GC_FIELD_CODICE_ESTERNO.' IS NOT NULL')->
                            get(GC_ENTITY);
                    $this->db->
                            where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->
                            where(GC_FIELD_CANCELLATO, 'f')->
                            where_not_in(GC_FIELD_CODICE_ESTERNO, $google_ids)->
                            where(GC_FIELD_DATA_MODIFICA.' >= (now() - interval \'30 days\')')->
                            where(GC_FIELD_CODICE_ESTERNO.' IS NOT NULL')->
                            update(GC_ENTITY, array(GC_FIELD_CANCELLATO => 't'));
                } else {
                    
                    $res = $this->db->
                            where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->
                            where(GC_FIELD_CANCELLATO, 'f')->
                            where_not_in(GC_FIELD_CODICE_ESTERNO, $google_ids)->
                            where(GC_FIELD_CODICE_ESTERNO.' IS NOT NULL')->
                            get(GC_ENTITY);
                    $this->db->
                            where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->
                            where(GC_FIELD_CANCELLATO, 'f')->
                            where_not_in(GC_FIELD_CODICE_ESTERNO, $google_ids)->
                            where(GC_FIELD_CODICE_ESTERNO.' IS NOT NULL')->
                            update(GC_ENTITY, array(GC_FIELD_CANCELLATO => 't'));
                }

                /* echo('<pre>');
                  print_r($res->row_array());
                  echo('</pre>'); */
                $eliminati_crm += $res->num_rows();
            }

            //Recupero tutti gli altri appuntamenti (che dovrebbero essere solo quelli su agos, non ancora inseriti in google e gli inserisco aggiornando con l'id di google)
            if ($data_da != '') {
                $appuntamenti = $this->db->
                        where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->
                        where(GC_FIELD_DATA_MODIFICA.' >= (now() - interval \'30 days\')')->
                        where(GC_FIELD_CODICE_ESTERNO.' IS NULL')->
                        get(GC_ENTITY)->result_array();
            } else {
                $appuntamenti = $this->db->
                        where(GC_FIELD_UTENTE, $sincronizzazione['google_calendar_utente'])->
                        where(GC_FIELD_CODICE_ESTERNO.' IS NULL')->
                        get(GC_ENTITY)->result_array();
            }
            foreach ($appuntamenti as $appuntamento) {
                usleep(50000);
                debug('Inserisco appuntamento su google');
                debug($appuntamento);
                $event = new Google_Service_Calendar_Event();
                $event->setSummary($appuntamento[GC_FIELD_TITOLO]);
                $event->setDescription($appuntamento[GC_FIELD_DESCRIZIONE]);
                $event->setLocation($appuntamento[GC_FIELD_LUOGO]);
                if ($appuntamento[GC_FIELD_GIORNATA_INTERA] == 'f') {
                $start = new Google_Service_Calendar_EventDateTime();
                $start->setDateTime(str_replace(' ', 'T', $appuntamento[GC_FIELD_DATA_DA]) . ".000+$fuso");
                $event->setStart($start);
                
                    $end = new Google_Service_Calendar_EventDateTime();
                    $end->setDateTime(str_replace(' ', 'T', $appuntamento[GC_FIELD_DATA_A]) . ".000+$fuso");
                    $event->setEnd($end);
                } else {
                    $date = explode(' ', $appuntamento[GC_FIELD_DATA_DA])[0];
                    $start = new Google_Service_Calendar_EventDateTime();
                    $start->setDate($date);
                    $event->setStart($start);
                    
                    $end = new Google_Service_Calendar_EventDateTime();
                    $end->setDate($date);
                    $event->setEnd($end);
                    
                    //$event->setEndTimeUnspecified(true);
                    
                }
                $createdEvent = $this->service->events->insert($calendar_to_sync->id, $event);
                //$createdEvent->getId();
                //$e = $g->addEvent($calendar_to_sync, $appuntamento[GC_FIELD_TITOLO], str_replace(' ', 'T', $appuntamento[GC_FIELD_DATA_DA]) . ".000+$fuso", str_replace(' ', 'T', $appuntamento[GC_FIELD_DATA_A]) . ".000+$fuso", $appuntamento[GC_FIELD_DESCRIZIONE]);

                if ($createdEvent->getId()) {
                    //aggiorno appuntamento con l'id google assegnato in modo da mantenere la sincronia
                    $this->db->
                            where(GC_ENTITY.'_id', $appuntamento[GC_ENTITY.'_id'])->
                            update(GC_ENTITY, array(GC_FIELD_CODICE_ESTERNO => $createdEvent->getId()));
                    //debug($e,1);
                    $inseriti_google++;
                } else {
                    debug("Impossibile sincronizzare l'appuntamento {$appuntamento[GC_FIELD_TITOLO]}.");
                    debug($createdEvent, true);
                    
                }
            }
            //debug($calendar_to_sync->id . ": agos(ins: $inseriti_crm, agg: $aggiornati_crm, eli: $eliminati_crm), google(ins: $inseriti_google, agg: $aggiornati_google, eli: $eliminati_google). <i>Non sono inclusi gli aggiornamenti su agos dellGC_FIELD_CODICE_ESTERNO.' dovuto all'inserimento su google...</i><br />");
            //$this->dati->new_report(R_CRON, null, $sincronizzazione['google_calendar_utente'], $sincronizzazione['sincronizzazione_user'] . ": agos(ins.: $inseriti_crm, agg.: $aggiornati_crm), google(ins.: $inseriti_google, agg.: $aggiornati_google)", CRON_SYNC);
            debug($sincronizzazione['google_calendar_utente']. " " . $calendar_to_sync->id . ": su crm, inseriti: $inseriti_crm, aggiornati: $aggiornati_crm, eliminati: $eliminati_crm; su google, inseriti: $inseriti_google, aggiornati: $aggiornati_google, eliminati: $eliminati_google");
            
            $this->db->query("UPDATE google_calendar SET google_calendar_last_sync = now() WHERE google_calendar_id = '{$sincronizzazione['google_calendar_id']}'");
        }




        exit('1');
    }

}
