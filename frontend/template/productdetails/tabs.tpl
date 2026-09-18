{if !empty($adpSpecsActive)}

{capture name="adp_specs_block"}
    {* Stylesheet wird einmalig in productdetails/details.tpl eingebunden *}
    <div class="adp-specs">
        <div class="adp-specs__grid">
            {include file='productdetails/svg_attributes.tpl' tplscope='details'}
            {include file='productdetails/snowboard_values.tpl' tplscope='details'}
        </div>
    </div>
{/capture}

{block name='tab-description-media-types' prepend}
    {$smarty.capture.adp_specs_block}
{/block}

{block name='productdetails-tabs-card-description-content' prepend}
    {$smarty.capture.adp_specs_block}
{/block}

{/if}
