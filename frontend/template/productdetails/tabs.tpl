{if !empty($adpSpecsActive)}

{capture name="adp_specs_block"}
    {* Stylesheet wird einmalig in productdetails/details.tpl eingebunden *}
    <div class="adp-specs">
        <div class="adp-specs__grid">
            {if !empty($adpSpecsCharacteristics) || !empty($adpFlex)}
                <div class="adp-specs__col">
                    {include file='productdetails/characteristics.tpl' tplscope='details'}
                    {include file='productdetails/flex.tpl' tplscope='details'}
                </div>
            {/if}
            {if !empty($adpWeight) || !empty($adpLevel) || !empty($adpSpecsDimensions)}
                <div class="adp-specs__col">
                    {include file='productdetails/fit.tpl' tplscope='details'}
                    {include file='productdetails/snowboard_values.tpl' tplscope='details'}
                </div>
            {/if}
        </div>
        {include file='productdetails/profile.tpl' tplscope='details'}
    </div>
{/capture}

{block name='tab-description-media-types' prepend}
    {$smarty.capture.adp_specs_block}
{/block}

{block name='productdetails-tabs-card-description-content' prepend}
    {$smarty.capture.adp_specs_block}
{/block}

{/if}
