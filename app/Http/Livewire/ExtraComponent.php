<?php

namespace App\Http\Livewire;

use App\Models\Extra;
use App\Models\Product;
use Livewire\Component;

class ExtraComponent extends Component
{
    public $activeTab = 'extras';

    public $product;

    public $active = false;

    public $relatedProducts;
    public $addedProducts = [];
    public $productSelected;
    public $productGroups = [];

    public $extraSelected;
    public $relatedExtras;
    public $addedExtras = [];
    public $extraGroups = [];
    public $extras;

    public $errorsHandling = [];
    public $oldExtraValues = [];
    public $oldProductValues = [];
    public $oldRemovableValues = [];

    public $removables;
    public $removableSelected;
    public $addedRemovables = [];
    public $removableGroups = [];
    public $removableProducts;

    public $listeners = ['switchBundleUpdated' => 'switchBundleUpdated'];

    public function mount()
    {
        $this->extras = Extra::all();
        $this->relatedProducts = Product::all();
        $this->removables = $this->extras;

        foreach($this->errorsHandling as $key => $value) {
            if(strpos($key, 'extra') !== false) {
                $this->activeTab = 'extras';
                $this->active = true;
            } elseif(strpos($key, 'product') !== false) {
                $this->activeTab = 'products';
                $this->active = true;
            } elseif(strpos($key, 'removable') !== false) {
                $this->activeTab = 'removables';
                $this->active = true;
            }
        }

        if($this->oldExtraValues) {
            foreach($this->oldExtraValues as $index => $oldExtraValue) {
                $this->extraGroups[] = [
                    'name' => $oldExtraValue['groupname'],
                    'limit' => $oldExtraValue['grouplimit'],
                    'min_limit' => $oldExtraValue['minlimit'],
                ];
                $this->addedExtras[$index] = [];
                if(isset($oldExtraValue['extras'])) {
                    foreach($oldExtraValue['extras'] as $extra) {
                        $this->addedExtras[$index][$extra['id']] = [
                            'id' => $extra['id'],
                            'name' => Extra::find($extra['id'])->title,
                            'price' => $extra['price'],
                            'order' => $extra['order'],
                        ];
                    }
                }
            }
        } elseif($this->product?->bundle?->extras) {
            foreach($this->product->bundles()->with(['extras', 'extraProducts'])->get() as $bundle) {
                if(count($bundle->extras) > 0) {
                    $this->extraGroups[] = [
                        'name' => $bundle->name,
                        'limit' => $bundle->limit,
                        'min_limit' => $bundle->min_limit,
                    ];
                    foreach($bundle->extras as $extra) {
                        $this->addedExtras[count($this->extraGroups)-1][$extra->id] = [
                            'id' => $extra->id,
                            'name' => $extra->title,
                            'price' => $extra->pivot->price,
                            'order' => $extra->pivot->order,
                        ];
                    }
                }
            }
        }
        if($this->oldProductValues) {
            foreach($this->oldProductValues as $index => $oldProductValue) {
                $this->productGroups[] = [
                    'name' => $oldProductValue['groupname'],
                    'limit' => $oldProductValue['grouplimit'],
                    'min_limit' => $oldProductValue['minlimit'],
                ];
                $this->addedProducts[$index] = [];
                if(isset($oldProductValue['products'])) {
                    foreach($oldProductValue['products'] as $product) {
                        $this->addedProducts[$index][$product['id']] = [
                            'id' => $product['id'],
                            'name' => Product::find($product['id'])->name,
                            'price' => $product['price'],
                            'order' => $product['order'],
                        ];
                    }
                }
            }
        } elseif ($this->product?->bundle?->extraProducts) {
            $this->productGroups = [];

            foreach ($this->product->bundles()->with(['extraProducts'])->get() as $bundle) {
                if (count($bundle->extraProducts) > 0) {
                    $this->productGroups[] = [
                        'name' => $bundle->name,
                        'limit' => $bundle->limit,
                        'min_limit' => $bundle->min_limit,
                    ];
                    foreach ($bundle->extraProducts as $product) {
                        $this->addedProducts[count($this->productGroups) - 1][$product->id] = [
                            'id' => $product->id,
                            'name' => $product->name, // Assuming "name" is the correct property.
                            'price' => $product->pivot->price,
                            'order' => $product->pivot->order,
                        ];
                    }
                }
            }
        }
        if($this->oldRemovableValues) {
            foreach($this->oldRemovableValues as $index => $oldRemovableValue) {
                $this->removableGroups[] = [
                    'name' => $oldRemovableValue['groupname'],
                    'limit' => $oldRemovableValue['grouplimit'],
                ];
                $this->addedRemovables[$index] = [];
                if(isset($oldRemovableValue['removables'])) {
                    foreach($oldRemovableValue['removables'] as $removable) {
                        $this->addedRemovables[$index][$removable['id']] = [
                            'id' => $removable['id'],
                            'name' => Extra::find($removable['id'])->title,
                            'order' => $removable['order'],
                        ];
                    }
                }
            }
        } elseif($this->product?->bundle?->removables) {
            foreach($this->product->bundles()->with(['removables'])->get() as $bundle) {
                if(count($bundle->removables) > 0) {
                    $this->removableGroups[] = [
                        'name' => $bundle->name,
                        'limit' => $bundle->limit
                    ];
                    foreach($bundle->removables as $removable) {
                        $this->addedRemovables[count($this->removableGroups)-1][$removable->id] = [
                            'id' => $removable->id,
                            'name' => $removable->title,
                            'order' => $removable->pivot->order,
                        ];
                    }
                }
            }
        }

    }

    public function switchBundleUpdated()
    {
        $this->active = !$this->active;
    }

    public function removeGroup($type, $index)
    {
        switch($type) {
            case 'extra':
                unset($this->extraGroups[$index]);
                unset($this->addedExtras[$index]);
                unset($this->extraSelected[$index]);
                $this->extraGroups = array_values($this->extraGroups);
                $this->addedExtras = array_values($this->addedExtras);
                break;
            case 'product':
                unset($this->productGroups[$index]);
                unset($this->addedProducts[$index]);
                unset($this->productSelected[$index]);
                $this->productGroups = array_values($this->productGroups);
                $this->addedProducts = array_values($this->addedProducts);
                break;
            case 'removable':
                unset($this->removableGroups[$index]);
                unset($this->addedRemovables[$index]);
                unset($this->removableSelected[$index]);
                $this->removableGroups = array_values($this->removableGroups);
                $this->addedRemovables = array_values($this->addedRemovables);
                break;
        }
    }

    public function addExtraGroup()
    {
        $lastIndex = count($this->extraGroups);

        $this->extraGroups[$lastIndex] = [
            'name' => '',
            'limit' => '',
            'min_limit' => '',
        ];
        $this->extraSelected[$lastIndex] = Extra::first()->id ?? null;

        $this->addedExtras[$lastIndex] = [];

    }

    public function addProductGroup()
    {
        $lastIndex = count($this->productGroups);
        $this->productGroups[$lastIndex] = [
            'name' => '',
            'limit' => '',
            'min_limit' => '',
        ];

        $this->productSelected[$lastIndex] = Product::first()->id;

        $this->addedProducts[$lastIndex] = [];
    }

    public function addRemovableGroup()
    {
        // $this->removableGroups[] = [
        //     'name' => '',
        //     'limit' => '',
        // ];

        // $this->removableSelected[count($this->removableGroups)-1] = Extra::first()->id;

        // $this->addedRemovables[count($this->removableGroups)-1] = [];
    }

    public function addExtra($id)
    {
        if(!@$this->extraSelected) return;
        if(!@$this->extraSelected[$id]) {
            return;
        }
        if(isset($this->addedExtras[$id][$this->extraSelected[$id]])) return;
        $extra = Extra::find($this->extraSelected[$id]);
        $this->addedExtras[$id][$this->extraSelected[$id]] = [
            'id' => $extra->id,
            'name' => $extra->title,
            'price' => $extra->price,
            'order' => $extra->order,
        ];

    }

    public function addProduct($id)
    {
        if(!@$this->productSelected) return;
        if(!@$this->productSelected[$id]) {
            return;
        }
        if(isset($this->addedProducts[$id][$this->productSelected[$id]])) return;
        $this->addedProducts[$id][$this->productSelected[$id]] = Product::find($this->productSelected[$id])->toArray();
    }

    public function addRemovable($id)
    {
        if(!@$this->removableSelected) return;
        if(!@$this->removableSelected[$id]) {
            return;
        }
        if(isset($this->addedRemovables[$id][$this->removableSelected[$id]])) return;
        $removable = Extra::find($this->removableSelected[$id]);
        $this->addedRemovables[$id][$this->removableSelected[$id]] = [
            'id' => $removable->id,
            'name' => $removable->title,
            'order' => $removable->order,
        ];
    }


    public function removeExtra($id)
    {
        unset($this->addedExtras[0][$id]);
    }

    public function removeProduct($id)
    {
        unset($this->addedProducts[0][$id]);
    }

    public function removeRemovable($id)
    {
        unset($this->addedRemovables[0][$id]);
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $this->dispatchBrowserEvent('group-added');

        return view('livewire.extra-component');
    }
}
