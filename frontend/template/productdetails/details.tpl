{block name='productdetails-details-stock' prepend}
    {assign var=adpL value=$oPlugin_artikel_details_plus->getLocalization()}

    {* Eigene Spalte in NOVAs Preis-Row: ohne col-Klasse würde das Markup als nacktes
       Flex-Item neben dem Preis landen und ihn schmaler machen. *}
    {if !empty($adpStock) || $adpCheaperActive || !empty($adpSpecsActive)}
        <div class="col col-12 adp-assets">
            <link rel="stylesheet" href="{$adpFrontendURL}css/artikel_details_plus.css?v={$oPlugin_artikel_details_plus->getMeta()->getVersion()}">
            {if !empty($adpStock)}
                <div class="adp-stock" style="--adp-stock-color: {$adpStock.color|escape:'html'}; --adp-stock-pct: {$adpStock.pct}%;">
                    <p class="adp-stock__label">
                        <span>{$adpL->getTranslation('artikel_details_plus_stock_text')|escape:'html'|replace:'%s':"<strong>{$adpStock.count|escape:'html'}</strong>"}</span>
                    </p>
                    <span class="adp-stock__track">
                        <span class="adp-stock__bar"></span>
                    </span>
                </div>
            {/if}
        </div>
    {/if}
{/block}

{* Der Button teilt sich die Zeile mit NOVAs "Frage zum Artikel" statt ein eigenes Band
   darüber zu belegen. *}
{block name='productdetails-details-question-on-item' prepend}
    {* Im Quickview rendert NOVA die Popups nicht – dort wäre der Button ohne Funktion *}
    {if $adpCheaperActive && empty($smarty.get.quickView)}
        {* gleiche ID-Logik wie NOVA popups.tpl: bei Variationskombinationen zählt der Kind-Artikel *}
        {assign var=adpModalId value=($Artikel->kArtikelVariKombi > 0) ? $Artikel->kArtikelVariKombi : $Artikel->kArtikel}
        {assign var=adpCheaperLabel value=$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_form_button')}
        <button type="button" class="btn adp-cheaper__btn" id="adp-cheaper-btn-{$adpModalId}" title="{$adpCheaperLabel|escape:'html'}" data-toggle="modal" data-target="#cheaper-{$adpModalId}">
            <span class="fa fa-tag" aria-hidden="true"></span>
            <span>{$adpCheaperLabel|escape:'html'}</span>
        </button>
    {/if}
{/block}
