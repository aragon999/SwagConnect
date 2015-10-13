{extends file="parent:frontend/checkout/items/product.tpl"}

{block name='frontend_checkout_cart_item_image_container_outer' prepend}
    {if $shopId && $bepadoShopInfo}
        <div class="bepado--bepado-image-article">
			<span class="bepado-image-article--text">
				Connect
			</span>
        </div>
    {/if}
{/block}

{block name="frontend_checkout_cart_item_details_inline"}
    {$smarty.block.parent}
    {if $shopId}
        <div class="bepado--additional-info">
            {if $showShippingCostsSeparately}
                <span class="bepado--cart-label">{s name="frontend_checkout_cart_bepado_dispatch"}Separater Versand{/s}</span>
            {/if}
            {if $bepadoShopInfo}
                <span class="bepado--cart-reseller">Artikel von {$bepadoShops[$shopId]->name}</span>
            {/if}
        </div>
    {/if}
{/block}