<div class="table_row bepado-dispatch-row">
	<div class="grid_6">
		<span class="title">
			{s name=bepado/checkout/dispatch_title}Versandkosten für die Lieferung {counter name=bepadoIndex} von {$bepadoShops|count + 1}{/s}
		</span>
		&nbsp;
	</div>

	<div class="grid_3">
		&nbsp;
	</div>

	<div class="grid_1">
		<select><option value="1">1</option></select>
	</div>

	<div class="grid_2 textright">
		{if $bepadoShippingCosts[$shopId]}
			{$bepadoShippingCostsOrg|currency}
		{elseif $bepadoShippingCostsOrg}
			{$bepadoShippingCostsOrg|currency}
		{/if}
	</div>

	<div class="grid_2 textright">
		<strong>{if $bepadoShippingCosts[$shopId]}
			{$bepadoShippingCostsOrg|currency}
		{elseif $bepadoShippingCostsOrg}
			{$bepadoShippingCostsOrg|currency}
		{/if}</strong>
	</div>
	<div class="clear"></div>
</div>