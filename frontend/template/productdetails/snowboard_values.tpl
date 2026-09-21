{* Dimensionen: Werte kommen aus Bootstrap::assignSnowboardSpecs() (Funktionsattribute mit Vater-Fallback) *}
{if !empty($adpSpecsDimensions)}
<section class="adp-panel adp-dims{if $adpSpecsBoard !== null} adp-dims--sketch{/if}">
    <h3 class="adp-panel__title">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_specs_heading_dimensions')|escape:'html'}</h3>

    <div class="adp-dims__body">
    {if $adpSpecsBoard !== null}
    {assign var=b value=$adpSpecsBoard}
    <div class="adp-dims__board">
        <svg class="adp-board__svg" viewBox="{$b.viewBox}" xmlns="http://www.w3.org/2000/svg" role="img"
             aria-label="Nose {$b.nose.value|escape:'html'} mm, Waist {$b.waist.value|escape:'html'} mm, Tail {$b.tail.value|escape:'html'} mm{if $b.length && isset($adpSpecsDimensions.laenge)}, {$adpSpecsDimensions.laenge.label|escape:'html'} {$b.length.label|escape:'html'}{/if}">
            <path class="adp-board__outline" d="{$b.path}"/>

            {if $b.inserts}
                <g class="adp-board__inserts adp-board__inserts--{$b.inserts.type}">
                    {foreach $b.inserts.slots as $slot}
                        <rect class="adp-board__slot" x="{$slot.x}" y="{$slot.y}" width="{$slot.w}" height="{$slot.h}" rx="{$slot.h / 2}"/>
                    {/foreach}
                    {foreach $b.inserts.holes as $hole}
                        <circle class="adp-board__hole" cx="{$hole.cx}" cy="{$hole.cy}" r="{$hole.r}"/>
                    {/foreach}
                </g>
            {/if}

            {if $b.length}
                <line class="adp-board__measure adp-board__measure--length" x1="{$b.length.x1}" y1="{$b.length.y}" x2="{$b.length.x2}" y2="{$b.length.y}"/>
                <line class="adp-board__tick" x1="{$b.length.x1}" y1="{$b.length.y - 6}" x2="{$b.length.x1}" y2="{$b.length.y + 6}"/>
                <line class="adp-board__tick" x1="{$b.length.x2}" y1="{$b.length.y - 6}" x2="{$b.length.x2}" y2="{$b.length.y + 6}"/>
                <text class="adp-board__label adp-board__label--length" x="300" y="{$b.length.y - 6}" text-anchor="middle">{$b.length.label|escape:'html'}</text>
            {/if}

            {foreach ['nose', 'waist', 'tail'] as $part}
                {assign var=m value=$b.$part}
                <line class="adp-board__measure" x1="{$m.x}" y1="{$m.y1}" x2="{$m.x}" y2="{$m.y2}"/>
                <line class="adp-board__tick" x1="{$m.x - 8}" y1="{$m.y1}" x2="{$m.x + 8}" y2="{$m.y1}"/>
                <line class="adp-board__tick" x1="{$m.x - 8}" y1="{$m.y2}" x2="{$m.x + 8}" y2="{$m.y2}"/>
                <text class="adp-board__label" x="{$m.x}" y="{$b.labelY}" text-anchor="middle">{$adpSpecsDimensions.$part.label|escape:'html'} {$m.value|escape:'html'} mm</text>
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
