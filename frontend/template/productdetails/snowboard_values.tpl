{* Dimensionen: Werte kommen aus Bootstrap::assignSnowboardSpecs() (Funktionsattribute mit Vater-Fallback) *}
{if !empty($adpSpecsDimensions)}
<section class="adp-panel adp-panel--wide adp-dims{if $adpSpecsBoard !== null} adp-dims--sketch{/if}">
    <h3 class="adp-panel__title">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_specs_heading_dimensions')|escape:'html'}</h3>

    <div class="adp-dims__body">
    {if $adpSpecsBoard !== null}
    <div class="adp-dims__board">
        <svg class="adp-board__svg" viewBox="0 0 600 220" xmlns="http://www.w3.org/2000/svg" role="img"
             aria-label="Nose {$adpSpecsBoard.nose.value|escape:'html'} mm, Waist {$adpSpecsBoard.waist.value|escape:'html'} mm, Tail {$adpSpecsBoard.tail.value|escape:'html'} mm">
            <path class="adp-board__outline" d="{$adpSpecsBoard.path}"/>
            {foreach ['nose', 'waist', 'tail'] as $part}
                {assign var=m value=$adpSpecsBoard.$part}
                <line class="adp-board__measure" x1="{$m.x}" y1="{$m.y1}" x2="{$m.x}" y2="{$m.y2}"/>
                <line class="adp-board__tick" x1="{$m.x - 8}" y1="{$m.y1}" x2="{$m.x + 8}" y2="{$m.y1}"/>
                <line class="adp-board__tick" x1="{$m.x - 8}" y1="{$m.y2}" x2="{$m.x + 8}" y2="{$m.y2}"/>
                <text class="adp-board__label" x="{$m.x}" y="200" text-anchor="middle">{$adpSpecsDimensions.$part.label|escape:'html'} {$m.value|escape:'html'} mm</text>
            {/foreach}
        </svg>
    </div>
    {/if}

    <table class="adp-dims__table">
        <tbody>
        {foreach $adpSpecsDimensions as $dim}
            <tr>
                <th scope="row">{$dim.label|escape:'html'}</th>
                <td>{$dim.value|escape:'html'}{if $dim.unit} {$dim.unit|escape:'html'}{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    </div>
</section>
{/if}
