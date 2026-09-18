{block name='productdetails-details-include-variation' append}
    {if !empty($adpCountdown)}
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

                    var countDownDate = new Date("{$adpCountdown.target|escape:'javascript'}").getTime();

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
    {if !empty($adpStock)}
        <style>
            .lagerbestand-anzeige {
                width: 100%;
                margin: 0 0 10px;
            }
            .lagerbestand-fortschritt {
                width: 100%;
                background-color: lightgrey;
                height: 20px;
                border-radius: 5px;
                overflow: hidden;
                border: 1px solid grey;
            }
            .lagerbestand-fortschritt .fortschritt {
                background-color: {$adpStock.color};
                width: {$adpStock.pct}%;
                height: 100%;
            }
        </style>
        <div class="lagerbestand-anzeige">
            <div>
                <span>{if $lang eq "eng"}Only {else}Nur noch {/if}<strong>{$adpStock.count}</strong>{if $lang eq "eng"} pieces available!{else} Stück verfügbar!{/if}</span>
            </div>
            <div class="lagerbestand-fortschritt">
                <div class="fortschritt"></div>
            </div>
        </div>
    {/if}
    {if $adpCheaperActive}
        {* gleiche ID-Logik wie NOVA popups.tpl: bei Variationskombinationen zählt der Kind-Artikel *}
        {assign var=adpModalId value=($Artikel->kArtikelVariKombi > 0) ? $Artikel->kArtikelVariKombi : $Artikel->kArtikel}
        <div class="col col-12">
            <div class="row" style="border-top: 1px solid #ebebeb; margin-right: 0px; margin-left: 0px; justify-content: flex-end;">
                <button type="button" class="btn btn-link question" id="adp-cheaper-btn-{$adpModalId}" title="{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_form_button')|escape:'html'}" data-toggle="modal" data-target="#cheaper-{$adpModalId}" style="margin-right: 0px; padding-right: 0px;">
                    <span class="fa fa-question-circle"></span>
                    <span class="d-none d-md-inline">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_form_button')}</span>
                </button>
            </div>
        </div>
    {/if}
{/block}
