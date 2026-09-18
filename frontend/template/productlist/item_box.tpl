{block name="productlist-index-include-price" append}
    {* $adpFeatureImagesActive / $adpFeatureIds kommen aus Bootstrap (HOOK_SMARTY_INC).
       Styles bleiben inline: auf Listenseiten wird das Plugin-Stylesheet nicht geladen. *}
    {if !empty($adpFeatureImagesActive) && !empty($adpFeatureIds)}
        <ul class="adp-feature-icons" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin: 8px 0 0; padding: 0; list-style: none;">
            {foreach $adpFeatureIds as $featureNumber}
                {if isset($Artikel->oMerkmale_arr.{$featureNumber})}
                    {foreach $Artikel->oMerkmale_arr.{$featureNumber}->oMerkmalWert_arr oMerkmal}
                        {if $oMerkmal->currentImagePath != null}
                            <li style="line-height: 0;" data-toggle="tooltip" data-placement="bottom" data-html="true" title="<strong>{$Artikel->oMerkmale_arr.{$featureNumber}->cName|escape:'html'}: {$oMerkmal->cWert|escape:'html'}</strong><br>{$oMerkmal->cBeschreibung|escape:'html'}">
                                <img width="35" height="35" src="{$ShopURL}/media/image/characteristicvalue/{$oMerkmal->id|intval}/lg/{$oMerkmal->currentImagePath|escape:'html'}" alt="{$oMerkmal->cWert|escape:'html'}" class="image" style="width: 35px; height: 35px; border-radius: 4px;" />
                            </li>
                        {/if}
                    {/foreach}
                {/if}
            {/foreach}
        </ul>
    {/if}
{/block}
