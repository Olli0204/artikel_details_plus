{block name='productdetails-popups' append}
    {block name='productdetails-popups-include-cheaper'}
        {modal id="cheaper-{$kArtikel}" title="Günstiger gesehen"}
            {include file='productdetails/cheaper.tpl' position='popup'}
        {/modal}
    {/block}
{/block}