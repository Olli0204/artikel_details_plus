{assign "shape" $Artikel->FunktionsAttribute.shape}
{assign "form" $Artikel->FunktionsAttribute.form}
{assign "waist" $Artikel->FunktionsAttribute.waist}
{assign "nose" $Artikel->FunktionsAttribute.nose}
{assign "tail" $Artikel->FunktionsAttribute.tail}

{if $shape && $form && $waist && $nose && $tail}
    <ul class="adp-snowboard-specs">
        <li>Form: {$form|escape}</li>
        <li>Shape: {$shape|escape}</li>
        <li>Waist: {$waist|escape} mm</li>
        <li>Nose: {$nose|escape} mm</li>
        <li>Tail: {$tail|escape} mm</li>
    </ul>
{/if}
