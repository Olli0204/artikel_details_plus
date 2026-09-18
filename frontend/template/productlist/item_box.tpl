{block name="productlist-index-include-price" append}
    {assign var=adpFeatureIds value=$oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_merkmalwerte')}
    {if $oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_merkmalbilder_aktiv') === 'Y' && is_array($adpFeatureIds)}
        <ul style="padding: 0px; list-style-type: none; display:flex; justify-content: center;">
            {foreach $adpFeatureIds as $featureNumber}
                {if isset ($Artikel->oMerkmale_arr.{$featureNumber})}
                    {foreach $Artikel->oMerkmale_arr.{$featureNumber}->oMerkmalWert_arr  oMerkmal}
                        {if $oMerkmal->currentImagePath != null}
                            <li data-toggle="tooltip" data-placement="bottom" style="background-color: rgba(0,0,0,0.0);" data-html="true" title="<strong>{$Artikel->oMerkmale_arr.{$featureNumber}->cName|escape:'html'}: {$oMerkmal->cWert|escape:'html'}</strong><br>{$oMerkmal->cBeschreibung|escape:'html'}">
                                <img width="35" height="35" src="{$ShopURL}/media/image/characteristicvalue/{$oMerkmal->id|intval}/lg/{$oMerkmal->currentImagePath|escape:'html'}" alt="{$oMerkmal->cWert|escape:'html'}" class="image" style="margin: 6px; background-color: rgba(0,0,0,0.0);" />
                            </li>
                        {/if}
                    {/foreach}
                {/if}     
            {/foreach}
        </ul>
    {/if}
{/block}