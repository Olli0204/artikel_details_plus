{if isset($Artikel->FunktionsAttribute.jib)}{assign "ecm_sb_jib" $Artikel->FunktionsAttribute.jib}{elseif isset($Artikel->VaterFunktionsAttribute.jib)}{assign "ecm_sb_jib" $Artikel->VaterFunktionsAttribute.jib}{/if}
{if isset($Artikel->FunktionsAttribute.jump)}{assign "ecm_sb_jump" $Artikel->FunktionsAttribute.jump}{elseif isset($Artikel->VaterFunktionsAttribute.jump)}{assign "ecm_sb_jump" $Artikel->VaterFunktionsAttribute.jump}{/if}
{if isset($Artikel->FunktionsAttribute.powder)}{assign "ecm_sb_powder" $Artikel->FunktionsAttribute.powder}{elseif isset($Artikel->VaterFunktionsAttribute.powder)}{assign "ecm_sb_powder" $Artikel->VaterFunktionsAttribute.powder}{/if}
{if isset($Artikel->FunktionsAttribute.carving)}{assign "ecm_sb_carving" $Artikel->FunktionsAttribute.carving}{elseif isset($Artikel->VaterFunktionsAttribute.carving)}{assign "ecm_sb_carving" $Artikel->VaterFunktionsAttribute.carving}{/if}
{if isset($Artikel->FunktionsAttribute.all_mountain)}{assign "ecm_sb_all_mountain" $Artikel->FunktionsAttribute.all_mountain}{elseif isset($Artikel->VaterFunktionsAttribute.all_mountain)}{assign "ecm_sb_all_mountain" $Artikel->VaterFunktionsAttribute.all_mountain}{/if}


{if isset($ecm_sb_jib) &&
    isset($ecm_sb_jump) &&
    isset($ecm_sb_powder) &&
    isset($ecm_sb_carving) &&
    isset($ecm_sb_all_mountain) }

<div class="pentarow text-center">    
    <center><script src="{$shopURL}plugins/artikel_details_plus/ecm_polygon_svg.js"></script></center>
    
    <div class="col-lg-8 col-lg-push-2 col-md-6 col-md-push-3 col-xs-10 col-xs-push-1">
        {*<h3> Snowboard {lang key="articelDetails-attributs" section="global"} </h3>*}
        <center><div id="ecm-attributes-svg"></div></center>
        <center><p class="ecm-attributes-description">-</p></center>
    </div>
    
        
    <script>
            $(function () {
                // [lable, description, value, max_value]
                let data = [['Carving', '', {$ecm_sb_carving}, 10], ['Jib', '', {$ecm_sb_jib}, 10], ['Powder', '', {$ecm_sb_powder}, 10], ['All-Mountain', '', {$ecm_sb_all_mountain}, 10], ['Jump', '', {$ecm_sb_jump}, 10]];
                let ecm_svg = new ECM_POLYGON_SVG(500, 400, 'black', 'red', 5, data);
                $("#ecm-attributes-svg").append(ecm_svg.getHTML());
                
                $( ".ecm_button" ).on( "mouseenter", function() {
                    let obj = JSON.parse($( this ).attr('attr-ecm-svg'));
                    $(".ecm-attributes-description").html(obj.title + ': ' + obj.value + '/' + obj.max_value);
                });
            }

            );
    </script>
    <style>
        .ecm_button text {
            color: black;
            font-weight: bolder;
            font-size: 12px;
        }
        .ecm_buttons .ecm_button path {
            opacity : 0;
        }

        .ecm_buttons .ecm_button:hover path.ecm_button_vis {
            opacity : 0.1;
        }
    </style>
</div>
{/if}
    
{if isset($Artikel->FunktionsAttribute.koerpergewicht_ab)}{assign "ecm_sb_gewab" $Artikel->FunktionsAttribute.koerpergewicht_ab}{/if}
{if isset($Artikel->FunktionsAttribute.koerpergewicht_bis)}{assign "ecm_sb_gewbis" $Artikel->FunktionsAttribute.koerpergewicht_bis}{/if}

{if isset($ecm_sb_gewab) &&
    isset($ecm_sb_gewbis)}
<p class="ecm-gewicht-title">
    {if $lang eq "eng"}
    Suggested Weight:
    {else}
    Empfohlenes Körpergewicht:
    {/if}
</p>
<div class="ecm-gewicht-list" style="" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{if $lang eq "eng"} Suggested Weight: {else}Empfohlenes Körpergewicht: {/if}<br>{$ecm_sb_gewab} - {$ecm_sb_gewbis} kg">
    {if !$isMobile}
        {assign "ecm_sb_gewlist" ["+", "35", "40", "45", "50", "55", "60", "65", "70", "75", "80", "85", "90", "95", "100", "+"]} {*this list needs to have an + at first and last element*}
    {else}
        {assign "ecm_sb_gewlist" ["+", "40", "50", "60", "70", "80", "90", "100", "+"]} {*this list needs to have an + at first and last element*}
    {/if}
    {foreach from=$ecm_sb_gewlist key=key item=item}
        {if $item == "+"}
            {if $key == 0}
                <div class="ecm-gewicht-item {if $ecm_sb_gewlist[1] > $ecm_sb_gewab}set{/if}">
                    {$item}
                </div>
            {else}
                {assign ecm_sb_gewlist_last $ecm_sb_gewlist[$ecm_sb_gewlist|count - 2]}
                <div class="ecm-gewicht-item {if $ecm_sb_gewlist_last < $ecm_sb_gewbis}set{/if}">
                    {$item}
                </div>
            {/if}
        {else}
        <div class="ecm-gewicht-item {if $item >= $ecm_sb_gewab && $item <= $ecm_sb_gewbis}set{/if}">
            {$item}
        </div>
        {/if}
    {/foreach}
</div>
    <style>
        .ecm-gewicht-item {
            width:{1/$ecm_sb_gewlist|count * 100}%;
        }
    </style>
{/if}

{if $oPlugin_artikel_details_plus->getConfig()->getValue("artikel_details_plus_fahrlevel_aktiv") === 'Y'}
    {if isset($Artikel->FunktionsAttribute.fahrlevel_ab)}{assign "ecm_sb_fahab" $Artikel->FunktionsAttribute.fahrlevel_ab}{elseif isset($Artikel->VaterFunktionsAttribute.fahrlevel_ab)}{assign "ecm_sb_fahab" $Artikel->VaterFunktionsAttribute.fahrlevel_ab}{/if}
    {if isset($Artikel->FunktionsAttribute.fahrlevel_bis)}{assign "ecm_sb_fahbis" $Artikel->FunktionsAttribute.fahrlevel_bis}{elseif isset($Artikel->VaterFunktionsAttribute.fahrlevel_bis)}{assign "ecm_sb_fahbis" $Artikel->VaterFunktionsAttribute.fahrlevel_bis}{/if}

    {if isset($ecm_sb_fahab) &&
        isset($ecm_sb_fahbis)}
    <p class="ecm-gewicht-title">
        {if $lang eq "eng"}
        Rider Skills:
        {else}
        Fahrlevel:
        {/if}
    </p>
    <div class="ecm-gewicht-list" style="" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{if $lang eq "eng"}Rider Skills: {else}Fahrlevel: {/if}<br>{if {$ecm_sb_fahab} != {$ecm_sb_fahbis}} {$ecm_sb_fahab} - {$ecm_sb_fahbis}">{else}{$ecm_sb_fahab}">{/if}
        {if not $oBrowser->bMobile}
            {assign "ecm_sb_fahlist" ["Beginner", "Advanced", "Professional"]} {*this list needs to have an + at first and last element*}
        {else}
            {assign "ecm_sb_fahlist" ["+", "40", "50", "60", "70", "80", "90", "100", "+"]} {*this list needs to have an + at first and last element*}
        {/if}
        {foreach from=$ecm_sb_fahlist key=key item=item}
            {if {$ecm_sb_fahab} == {$ecm_sb_fahbis}}
                {if {$key} == 0 && {$ecm_sb_fahab} == "Beginner"}
                    <div class="ecm-gewicht-item set">
                        {$item}
                    </div>
                {/if}
                {if {$key} == 0 && {$ecm_sb_fahab} != "Beginner"}
                    <div class="ecm-gewicht-item">
                        {$item}
                    </div>
                {/if}

                {if {$key} == 1 && {$ecm_sb_fahab} == "Advanced"}
                    <div class="ecm-gewicht-item set">
                        {$item}
                    </div>
                {/if}
                {if {$key} == 1 && {$ecm_sb_fahab} != "Advanced"}
                    <div class="ecm-gewicht-item">
                        {$item}
                    </div>
                {/if}

                {if {$key} == 2 && {$ecm_sb_fahbis} == "Professional"}
                    <div class="ecm-gewicht-item set">
                        {$item}
                    </div>
                {/if}
                {if {$key} == 2 && {$ecm_sb_fahbis} != "Professional"}
                    <div class="ecm-gewicht-item">
                        {$item}
                    </div>
                {/if}
            {else}
                {if {$key} == 0 && {$ecm_sb_fahab} == "Beginner"}
                    <div class="ecm-gewicht-item set">
                        {$item}
                    </div>
                {/if}
                {if {$key} == 0 && {$ecm_sb_fahab} != "Beginner"}
                    <div class="ecm-gewicht-item">
                        {$item}
                    </div>
                {/if}



                {if {$key} == 1 && (({$ecm_sb_fahbis} || {$ecm_sb_fahab}) == "Advanced")}
                    <div class="ecm-gewicht-item set">
                        {$item}
                    </div>
                {/if}
                {if {$key} == 1 && ({$ecm_sb_fahbis} || {$ecm_sb_fahab}) != "Advanced"}
                    <div class="ecm-gewicht-item">
                        {$item}
                    </div>
                {/if}


                {if {$key} == 2 && {$ecm_sb_fahbis} == "Professional"}
                    <div class="ecm-gewicht-item set">
                        {$item}
                    </div>
                {/if}
                {if {$key} == 2 && {$ecm_sb_fahbis} != "Professional"}
                    <div class="ecm-gewicht-item">
                        {$item}
                    </div>
                {/if}
            {/if}
        {/foreach}
    </div>
        <style>
            .ecm-gewicht-item {
                width:{1/$ecm_sb_fahlist|count * 100}%;
            }
        </style>
    {/if}
{/if}