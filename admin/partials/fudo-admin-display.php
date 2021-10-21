<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Fudo
 * @subpackage Fudo/admin/partials
 */
?>
<style type="text/css">
  .fudo > ul > li {
    font-weight: bold;
  }
  .fudo li {
    margin: 0;
    padding: 0;
  }
  .fudo ul > li > ul{
    font-weight: normal;
    margin-left: 5px;
  }
</style>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="fudo">
  <a href="admin.php?page=wc-settings&tab=integration&section=fudo">Settings</a>
  <h1>Hola FUDO!!!</h1>
  <ul>
    <li>Clientes
      <ul>
        <li>Listado de clientes</li>
        <li>Crear un cliente</li>
        <li>Detalles de un cliente</li>
        <li>Actualizar un cliente</li>
        <!--
          GET
          ​/customers
          POST
          ​/customers
          GET
          ​/customers​/{id}
          PATCH
          ​/customers​/{id}
        -->
      </ul>
    </li>
    <li>Ingredientes
      <ul>
        <li>Listado de ingredientes</li>
        <!--
          GET
          ​/ingredients
        -->
      </ul>
    </li>
    <li>Categorías de ingredientes
      <ul>
        <li>Listado de categorías de ingredientes</li>
      </ul>
      <!--
        GET
        ​/ingredient-categories
      -->
    </li>
    <li>Productos
      <ul>
        <li>Listado de productos</li>
        <li><?php
          $fudoClient = new Fudo_Client(false, false);
          $products = $fudoClient->get_products();
				?>
          <table id="products"></table>
          <script type="application/javascript">
            var products = <?php echo $products?>;
            var table=document.querySelector("#products");
            var tr,td;
            var valor = function(valor_){
              if(typeof valor_ === typeof []){
                var response = '';
                for(var index in valor_){
                  response +='<br>'+index+': '+valor(valor_[index]);
                }
                return response;
              }else if(typeof valor_ === typeof {}){
                var response = '';
                for(var item in valor_){
                  response += item+': '+valor(valor_[item]);
                }
                return response;
              }else return valor_
            };
            var fields=["id","favourite","minStock","name","price","cost","stock","stockControl","ignoreAvailability","active","iconName","description","providerId","productCategoryId","productTypeId","unitId","productProportions","comboGroups","enableOnlineMenu","enableQrMenu"];
            tr=document.createElement("tr");
            for(var field in fields) {
              td=document.createElement("th");
              td.innerHTML=valor(fields[field]);
              tr.appendChild(td);
            }
            table.appendChild(tr);
            for(var id in products){
              tr=document.createElement("tr");
              for(var field in products[id]){
                if(['code','proportions','kitchenId','image'].indexOf(field)>-1)continue;
                td=document.createElement("td");
                td.innerHTML=valor(products[id][field]);
                tr.appendChild(td);
              }
              table.appendChild(tr);
            }
          </script>
        </li>
        <li>Crear un producto</li>
        <li>Detalle del producto</li>
        <li>Actualizar un producto</li>
        <!--
          GET
          ​/products
          POST
          ​/products
          GET
          ​/products​/{id}
          PATCH
          ​/products​/{id}
        -->
      </ul>
    </li>
    <li>Categorías de productos
      <ul>
        <li>Listado de categorías de productos</li>
        <li><?php
					$categories = $fudoClient->get_categories();
					?>
          <table id="categories"></table>
          <script type="application/javascript">
            var categories = <?php echo $categories?>;
            var table=document.querySelector("#categories");
            var tr,td;
            fields=["id","kitchenId","name","parentId","enableMobile","enableOnlineMenu","enableQrMenu","subcategories","taxes"];
            tr=document.createElement("tr");
            for(var field in fields) {
              td=document.createElement("th");
              td.innerHTML=valor(fields[field]);
              tr.appendChild(td);
            }
            table.appendChild(tr);
            for(var id in categories){
              tr=document.createElement("tr");
              for(var field in categories[id]){
                if([/*'code','proportions','kitchenId','image'*/].indexOf(field)>-1)continue;
                td=document.createElement("td");
                td.innerHTML=valor(categories[id][field]);
                tr.appendChild(td);
              }
              table.appendChild(tr);
            }
          </script>
        </li>
        <li>Crear una categoría de producto</li>
        <li>Detalles de una categoría de producto</li>
        <li>Actualizar una categoría de producto</li>
        <!--
          GET
          ​/product-categories
          POST
          ​/product-categories
          GET
          ​/product-categories​/{id}
          PATCH
          ​/product-categories​/{id}
        -->
      </ul>
    </li>
    <li>Órdenes
      <ul>
        <li>Crear una orden</li>
        <!--
          POST
          ​/orders
        -->
      </ul>
    </li>
    <li>Ventas
      <ul>
        <li>Listado de ventas</li>
        <li>Detalles de una venta</li>
        <!--
          GET
          ​/sales
          GET
          ​/sales​/{id}
        -->
      </ul>
    </li>
  </ul>
</div>