<?php
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
class AssetsImportSeeder extends Seeder{

  var $file;
  var $admin;
  var $email = 'manuel.garcia@crowdint.com';

  public function setFileName($name){
    $this->file = $name;
  }
  public function setEmail($email){
    $this->email = $email;
  }

  public function run(){
    if(is_null($this->file))
      return 'Please set the file name using the method: setFileName(filename.csv);';

    DB::table('assets')->truncate();
    DB::table('models')->truncate();
    DB::table('manufacturers')->truncate();
    DB::table('asset_logs')->truncate();

    $this->admin = User::where('email', '=', $this->email)->firstOrFail();
    $location = Location::where('name', '=', 'Crowd Colima')->get();
    $location = $location[0];
    $assets = [];

    echo "Starting to import the  csv file\n";

    Excel::load("csv/$this->file", function($reader)use($location, $assets) {
      foreach($reader->get() as $row){

        $costo = preg_replace('/[^0-9.]*/','', $row['costo']);
        $serie = empty($row['numero_de_serie']) ? 'CROWDINT' : trim($row['numero_de_serie']);
        $asset_tag = empty($row['assettag']) ? 'CROWDINT' : trim($row['assettag']);
        $notes  = '';
        $user = NULL;
        if(!empty($row['procesador']))
          $notes .= "Processor: ". $row['procesador']."\n";
        if(!empty($row['tarjeta_grafica']))
          $notes .= "Graphic card: ".$row['tarjeta_grafica']."\n";
        if(!empty($row['memoria_ram']))
          $notes .= "Memory Ram: ".$row['memoria_ram']."\n";
        if(!empty($row['disco_duro']))
          $notes .= "Hard Disk: ".$row['disco_duro']."\n";
        if(!empty($row['ano_de_fabricacion']))
          $notes .= "Year of manufacture: ".$row['ano_de_fabricacion']."\n";

        $username = trim(implode('.', array_slice(explode(' ', $row['asignado_a']), 0,2)));
        $model_id = $this->getModelId($row);


        if(!empty($username))
          $user = User::where('email', 'like', "%$username%")->first();

        if(empty($costo))
          $costo = 0;

        $asset = array(
          'name' => $this->normalizeString($row['descripcion']),
          'status_id' => 1,
          'model_id' => $model_id,
          'user_id' 		=> $this->admin->id,
          'serial' => $serie,
          'purchase_cost' =>$costo,
          'rtd_location_id' => $location->id,
          'asset_tag' => $asset_tag,
          'notes' => $notes,
          'assigned_to' => NULL
        );
        if(!is_null($user)){
          $asset['assigned_to'] = $user->id;
        }
        array_push($assets, $asset);
      }
      // Delete all the old data
      DB::table('assets')->truncate();
      // // Insert the new posts
      Asset::insert($assets);
    });
  }
  public function findOrCreateManufacturer($name){
    if(empty($name)){
     return  NULL;
    }
    $manufacturer =  Manufacturer::where('name', '=', $name)->get()->first();
    if(is_null($manufacturer)){
      echo "Creating new manufacture $name\n";
      $manufacturer = new Manufacturer;
      $manufacturer->name = $name;
      $manufacturer->user_id = $this->admin->id;
      $manufacturer->save();
    }
    return $manufacturer->id;
  }

  public function getModelId($row){
    $modelname = $this->normalizeString($row['descripcion']);
    if(empty($modelname))
      return 1;

    $model = Model::where('name', '=', $modelname)->get()->first();
    if(is_null($model)){
      echo "Creating new model $modelname\n";
      $manufacturer_name = $this->normalizeString($row['marca']);
      $manufacturer_id = $this->findOrCreateManufacturer($manufacturer_name);
      $model = new Model;
      $model->name = $modelname;
      $model->manufacturer_id = is_null($manufacturer_id) ? 1 : $manufacturer_id;
      $model->category_id = 1;
      $model->user_id = $this->admin->id;
      $model->save();
    }
    return $model->id;

  }

  public function normalizeString($string){
    return trim(
      ucfirst(
        strtolower(
          $this->stripAccents($string)
        )
      )
    );
  }

  public function stripAccents($str) {
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
  }

}
