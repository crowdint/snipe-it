<?php
class AssetsImportSeeder extends Seeder{

    private $fields = array(
        'asset_tag'         => 'asset_tag',
        'asset_name'        => 'asset_name',
        'status'            => 'status',
        'purchase_cost'     => 'purchase_cost',
        'year'              => 'year',
        'month'             => 'month',
        'day'               => 'day',
        'dep_eol'           => 'dep_eol',
        'asset_accessories' => 'asset_accessories',
        'model'             => 'model',
        'manufacturer'      => 'manufacturador',
        'serial_number'     => 'numero_de_serie',
        'pulgades'          => 'pulgadas',
        'memory_ram'        => 'memoria_ram',
        'processor'         => 'procesador',
        'graphic_card'      => 'tarjeta_grafica',
        'hard_disk'         => 'disco_duro',
        'fabrication_year'  => 'ano_de_fabricacion',
        'assigned_to'       => 'asignado_a',
        'bamboo'            => 'bamboo',
        'no_import'         => 'no_import'
    );

    var $file;
    var $admin;
    var $email = 'manuel.garcia@crowdint.com';
    var $status_ids = array();

    public function __construct()
    {
        $collection_statuses = Statuslabel::whereIn('name',['Pending', 'Ready to Deploy'])->get();
        foreach($collection_statuses as $status){
            $this->status_ids[$status->name] = $status->id;
        }
    }

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
        DB::table('accessories')->truncate();
        DB::table('accessories_users')->truncate();

        $this->admin = User::where('email', '=', $this->email)->firstOrFail();
        $location = Location::where('name', '=', 'Crowd Colima')->get();
        $location = $location[0];
        $assets = [];
        $accessories = [];

        echo "Starting to import the  csv file\n";

        Excel::load("csv/$this->file", function($reader)use($location, $assets) {
            foreach($reader->get() as $row){

                $serie = empty($row[$this->fields['serial_number']]) ? 'CROWDINT' : trim($row[$this->fields['serial_number']]);
                $asset_tag = empty($row[$this->fields['asset_tag']]) ? 'CROWDINT' : trim($row[$this->fields['asset_tag']]);
                $notes  = $this->buildNotes($row);
                $user = $this->findUser($row);
                $model_id = $this->getModelId($row);
                $costo = $this->getCost($row);
                $status_id = $this->getStatusId($row);

                //TODO: store categories before assign to the users;
                if($row[$this->fields['asset_accessories']] === 'x'){
                    $this->createOrUpdateAccessory($row);
                }else{

                    $asset = array(
                        'name' => $this->normalizeString($row[$this->fields['asset_name']]),
                        'status_id' => $status_id,
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

            }
            Asset::insert($assets);
        });
    }

    public function getStatusId($row){
       return  empty(trim($row[$this->fields['status']])) ? $this->status_ids['Ready to Deploy'] : $this->status_ids['Pending'];
    }

    public function createOrUpdateAccessory($row){

        $user = $this->findUser($row);
        $name = $this->normalizeString($row[$this->fields['asset_name']])
            . " " . $this->normalizeString($row[$this->fields['model']])
            . " " .  $this->normalizeString($row[$this->fields['manufacturer']]);
        $accessory = Accessory::where('name', '=', $name)->get()->first();

        if(is_null($accessory)){
            $accessory = new Accessory;
            $accessory->category_id = $this->findOrCreateCategoryId($row);
            $accessory->name = $name;
            $accessory->qty = 0;
            if($accessory->save()){
                echo "Created new Accesory $accessory->name \n";
            }
        }

        $accessory->qty+=1;
        $accessory->save();

        if(!is_null($user)){
            if($accessory->users()->save($user)){
                echo "Associated Accessory: $accessory->name to User: $user->email\n";
            }
        }

    }

    public function findOrCreateCategoryId($row){
        $category = Category::where('name', '=', $row[$this->fields['asset_name']])->get()->first();
        if(is_null($category)){
            $category                     = new Category;
            $category->name               = $row[$this->fields['asset_name']];
            $category->user_id            = $this->admin->id;
            $category->use_default_eula   = 0;
            $category->require_acceptance = 0;
            $category->deleted_at         = NULL;

            if($category->save()){
                echo "Created new Category $category->name\n";
            }
        }
        return $category->id;
    }

    public function getCost($row){
        $cost = preg_replace('/[^0-9.]*/','', $row[$this->fields['purchase_cost']]);
        return empty($cost) ? 0 : $cost;
    }

    public function findUser($row){
        $username = trim(implode('.', array_slice(explode(' ', $row[$this->fields['assigned_to']]), 0,2)));

        return empty($username) ?  NULL : User::where('email', 'like', "%$username%")->first();
    }

    public function buildNotes($row){
        $notes = '';
        if(!empty($row[$this->fields['processor']]))
            $notes .= "Processor: ". $row[$this->fields['processor']]."\n";
        if(!empty($row[$this->fields['graphic_card']]))
            $notes .= "Graphic card: ".$row[$this->fields['graphic_card']]."\n";
        if(!empty($row[$this->fields['memory_ram']]))
            $notes .= "Memory Ram: ".$row[$this->fields['memory_ram']]."\n";
        if(!empty($row[$this->fields['hard_disk']]))
            $notes .= "Hard Disk: ".$row[$this->fields['hard_disk']]."\n";
        if(!empty($row[$this->fields['fabrication_year']]))
            $notes .= "Year of manufacture: ".$row[$this->fields['fabrication_year']]."\n";
        return $notes;
    }

    public function findOrCreateManufacturer($name){
        if(empty($name)){
            return  NULL;
        }
        $manufacturer =  Manufacturer::where('name', '=', $name)->get()->first();
        if(is_null($manufacturer)){
            $manufacturer = new Manufacturer;
            $manufacturer->name = $name;
            $manufacturer->user_id = $this->admin->id;
            if($manufacturer->save()){
                echo "Created new manufacture $name\n";
            };
        }
        return $manufacturer->id;
    }

    public function getModelId($row){
        $modelname = $this->normalizeString($row[$this->fields['asset_name']]);
        if(empty($modelname))
            return 1;

        $model = Model::where('name', '=', $modelname)->get()->first();
        if(is_null($model)){
            $manufacturer_name = $this->normalizeString($row[$this->fields['manufacturer']]);
            $manufacturer_id = $this->findOrCreateManufacturer($manufacturer_name);
            $model = new Model;
            $model->name = $modelname;
            $model->manufacturer_id = is_null($manufacturer_id) ? 1 : $manufacturer_id;
            $model->category_id = 1;
            $model->modelno = $row[$this->fields['model']];
            $model->user_id = $this->admin->id;
            if($model->save()){
                echo "Created new model $modelname\n";
            };
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
