{block name='productdetails-details-stock' prepend}
    {assign var=adpL value=$oPlugin_artikel_details_plus->getLocalization()}

    {if !empty($adpStock) || $adpCheaperActive || !empty($adpSpecsActive)}
        <link rel="stylesheet" href="{$adpFrontendURL}css/artikel_details_plus.css?v={$oPlugin_artikel_details_plus->getMeta()->getVersion()}">
    {/if}

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

    {if $adpCheaperActive}
        {* gleiche ID-Logik wie NOVA popups.tpl: bei Variationskombinationen zählt der Kind-Artikel *}
        {assign var=adpModalId value=($Artikel->kArtikelVariKombi > 0) ? $Artikel->kArtikelVariKombi : $Artikel->kArtikel}
        {assign var=adpCheaperLabel value=$adpL->getTranslation('artikel_details_plus_form_button')}
        <div class="col col-12">
            <div class="adp-cheaper">
                <button type="button" class="btn btn-link question adp-cheaper__btn" id="adp-cheaper-btn-{$adpModalId}" title="{$adpCheaperLabel|escape:'html'}" data-toggle="modal" data-target="#cheaper-{$adpModalId}">
                    <span class="fa fa-question-circle"></span>
                    <span>{$adpCheaperLabel|escape:'html'}</span>
                </button>
            </div>
        </div>
    {/if}
{/block}
