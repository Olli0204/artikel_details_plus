{block name='productdetails-popups' append}
    {block name='productdetails-popups-include-cheaper'}
        {if $adpCheaperActive}
            {assign "adp_cheaper_title" $oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_cheaper_title')}
            {* gleiche ID-Logik wie in details.tpl: bei Variationskombinationen zählt der Kind-Artikel *}
            {assign var=adpModalId value=($Artikel->kArtikelVariKombi > 0) ? $Artikel->kArtikelVariKombi : $Artikel->kArtikel}
            {modal id="cheaper-{$adpModalId}" title=$adp_cheaper_title}
                {include file='productdetails/cheaper.tpl' position='popup'}
            {/modal}
        {/if}
    {/block}
{/block}
