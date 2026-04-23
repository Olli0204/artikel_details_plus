


{assign "shape" $Artikel->FunktionsAttribute.shape}
{assign "form" $Artikel->FunktionsAttribute.form}
{assign "waist" $Artikel->FunktionsAttribute.waist}
{assign "nose" $Artikel->FunktionsAttribute.nose}
{assign "tail" $Artikel->FunktionsAttribute.tail}

{if $shape && $form && $waist && $nose && $tail}
    <div class="c-snoboard-viz"
        data-shape="{$shape|escape}"
        data-form="{$form|escape}"
        data-waist="{$waist|escape}"
        data-nose="{$nose|escape}"
        data-tail="{$tail|escape}">
        <div class="c-snoboard-viz__canvas" aria-label="Snowboard Visualization"></div>
        <div class="c-snoboard-viz__legend">
            <span>Form: {$form}</span>
            <span>Shape: {$shape}</span>
            <span>Waist: {$waist} mm</span>
            <span>Nose: {$nose} mm</span>
            <span>Tail: {$tail} mm</span>
        </div>
    </div>
{/if}