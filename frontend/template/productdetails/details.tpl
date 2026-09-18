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
                <span>{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_stock_text')|escape:'html'|replace:'%s':"<strong>{$adpStock.count|escape:'html'}</strong>"}</span>
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
