{block name='productdetails-popups' append}
    {block name='productdetails-popups-include-cheaper'}
        {assign "adp_cheaper_title" $oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_cheaper_title')}
        {modal id="cheaper-{$kArtikel}" title=$adp_cheaper_title}
            {include file='productdetails/cheaper.tpl' position='popup'}
        {/modal}
    {/block}
{/block}