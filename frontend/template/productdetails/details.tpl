{block name='productdetails-details-include-variation' append}
    {if $oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_countdown_aktiv') === 'on' && $Artikel->Preise->Sonderpreis_aktiv}
        <style>
            .countdownbox {
                width: 100%;
                background-color: rgb(255, 165, 79, 0.3);
                text-align: center;
                border: 2px solid #FFA54F;
                border-radius: 5px;
                margin: 10px 0px 20px 0px;
            }

            #countdownbox {
                display: none;
            }
        </style>
            <script>
                window.addEventListener('load', function () {

                    var countDownDate = new Date("{$oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_countdown_date')}T{$oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_countdown_time')}").getTime();

                    var timer = document.getElementById("countdownbox");
                    var x = setInterval(function () {
                        
                        var now = new Date().getTime();
                        
                        var distance = countDownDate - now;
                        
                        // Berechnungen der Zeiten auf die verschiedenen Einheiten
                        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        
                        //Ausgabe der Ergebnisse
                        document.getElementById("dayid").innerHTML = days;
                        document.getElementById("hourid").innerHTML = hours;
                        document.getElementById("minuteid").innerHTML = minutes;
                        document.getElementById("secondid").innerHTML = seconds;

                        if (distance > 0) {
                            timer.style.display = "block";
                        } else {
                            clearInterval(x);
                            timer.style.display = "none";
                        }

                    }, 1000);
                });
        </script> 

        <div id="countdownbox" class="countdownbox">
            <span style="font-size: 20px;">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_countdown_heading')}</span>
            <table style="display: flex; justify-content: center;">
                <tr>
                    <th>Tage</th>
                    <th>Stunden</th>
                    <th>Minuten</th>
                    <th>Sekunden</th>
                </tr>
                <tr style="font-size: 18px; font-weight: bold;">
                    <td style="padding: 0 30px 0 30px;" id="dayid">00</td>
                    <td style="padding: 0 30px 0 30px;" id="hourid">00</td>
                    <td style="padding: 0 30px 0 30px;" id="minuteid">00</td>
                    <td style="padding: 0 30px 0 30px;" id="secondid">00</td>
                </tr>
            </table>

        </div>
    {/if}  

{/block}

{block name='productdetails-details-stock' prepend}
    <style>
        .lagerbestand-anzeige {
            margin-top: 0px;
            width: 100%;
            margin-left: 20px;
            margin-right: 20px;
            margin-bottom: 10px;
        }
        .lagerbestand-fortschritt {
            width: 100%;
            background-color: lightgrey;
            height: 20px;
            border-radius: 5px;
            overflow: hidden;
            border: 1px solid grey;
        }
        .fortschritt {
            background-color: {$oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_lagerbestand_farbe')};
            width: {($Artikel->fLagerbestand / $oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_lagerbestand_wert')) * 100}%;
            height: 100%;
        }


    </style>
        {if ($Artikel->fLagerbestand < $oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_lagerbestand_wert')) && $oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_lagerbestand_aktiv')}
            <div class="lagerbestand-anzeige">
                <div>
        <span>{if $lang eq "eng"}Only {else}Nur noch {/if}<strong>{$Artikel->fLagerbestand}</strong>{if $lang eq "eng"} pieces available!{else} Stück verfügbar!{/if}</span>
                </div>
                <div class="lagerbestand-fortschritt">
                    <div class="fortschritt"></div>
                </div>
            </div>
        {/if}
        <div class="col col-12">
            <div class="row" style="border-top: 1px solid #ebebeb; margin-right: 0px; margin-left: 0px; justify-content: right;" >
                <button type="button" class="btn btn-link question" id="z{$Artikel->kArtikel}" title="Günstiger gesehen" data-toggle="modal" data-target="#cheaper-{$Artikel->kArtikel}" style="margin-right: 0px; padding-right: 0px;">
                    <span class="fa fa-question-circle"></span>
                    <span class="hidden-xs hidden-sm">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_form_button')}</span>
                </button>
            </div>
        </div>
    {/block}
