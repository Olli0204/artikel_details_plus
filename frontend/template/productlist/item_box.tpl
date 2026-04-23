{block name="productlist-index-include-price" append}
    {if $oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_merkmalbilder_aktiv')}
        <ul style="padding: 0px; list-style-type: none; display:flex; justify-content: center;">
            {foreach from=$oPlugin_artikel_details_plus->getConfig()->getValue('artikel_details_plus_merkmalwerte') item=featureNumber}
                {if isset ($Artikel->oMerkmale_arr.{$featureNumber})}
                    {foreach $Artikel->oMerkmale_arr.{$featureNumber}->oMerkmalWert_arr  oMerkmal}
                        {if $oMerkmal->currentImagePath != null}
                            <li data-toggle="tooltip" data-placement="bottom" style="background-color: rgba(0,0,0,0.0);" data-html="true" title="<strong>{$Artikel->oMerkmale_arr.{$featureNumber}->cName}: {$oMerkmal->cWert} </strong><br>{$oMerkmal->cBeschreibung}">
                                <img width="35" height="35" src="{$shopURL}/media/image/characteristicvalue/{$oMerkmal->id}/lg/{$oMerkmal->currentImagePath}" class="image" style="margin: 6px; background-color: rgba(0,0,0,0.0);" />
                            </li>
                        {/if}
                    {/foreach}
                {/if}     
            {/foreach}
        </ul>
    {/if}
{/block}