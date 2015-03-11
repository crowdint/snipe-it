<?php
class AssetsImportSeeder extends Seeder{

  public function run(){
    $location = Location::where('name', '=', 'Crowd Colima')->get();
    $location = $location[0];
    $assets = [];
    Excel::load('csv/assets.csv', function($reader)use($location, $assets) {
      foreach($reader->get() as $row){

        $costo = preg_replace('/[^0-9.]*/','', $row['costo']);
        $serie = empty($row['numero_de_serie']) ? 'CROWDINT' : trim($row['numero_de_serie']);
        $asset_tag = empty($row['assettag']) ? 'CROWDINT' : trim($row['assettag']);

        if(empty($costo))
          $costo = 0;

        $assets[] = array(
          'name' => trim($row['descripcion']),
          'status_id' => 1,
          'model_id' => 1,
          'user_id' 		=> 2,
          'serial' => $serie,
          'purchase_cost' =>$costo,
          'rtd_location_id' => $location->id,
          'asset_tag' => $asset_tag
        );
      }
        // Delete all the old data
        DB::table('assets')->truncate();
        // Insert the new posts
        Asset::insert($assets);
    });
  }
}

  // [assettag] => 1035
  // [descripcion] => macbook pro 13 pulgadas
  // [se_compro_por] => USA
  // [factura] =>
  // [costo] => 19,948.52
  // [ano] =>
  // [mes] =>
  // [dia] =>
  // [no_de_folio] => 000-0753
  // [fecha_de_baja] =>
  // [modelo] =>
  // [marca] => APPLE
  // [numero_de_serie] => C02KW1ARFGM8
  // [memoria_ram] => 8gb
  // [procesador] => 3ghz intel core i7
  // [tarjeta_grafica] => intel HD graphics 4000 1024mb
  // [disco_duro] => 500 gb
  // [ano_de_fabricacion] => 2013
  // [asignado_a] => FERNANDO PERALES
  // [proyecto] =>
