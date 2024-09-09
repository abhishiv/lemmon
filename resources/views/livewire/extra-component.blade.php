<div>
    <div class="col-sm-12" wire:key="{{ rand(10000, 20000) }}">

        @if ($active)
            @if(empty($extras))
                There are no Extras created.
            @else
                <div class="tab">
                    <button type="button" wire:click="switchTab('extras')"
                            class="tablinks {{ $activeTab == 'extras' ? 'activeTab' : '' }}">Extras
                    </button>
                    <button type="button" wire:click="switchTab('products')"
                            class="tablinks {{ $activeTab == 'products' ? 'activeTab' : '' }}">Products
                    </button>
                </div>
                <div id="extras" class="tabcontent" style="display: {{ $activeTab == 'extras' ? 'block' : 'none' }};">
                    <div class="col-sm-12">
                        <div class="custom">
                            <div class="add-extra-group-button" style="display: flex;gap: 6px;">
                                <div class="button-container" style="margin-top:0!important;width:100%">
                                    <button type="button" wire:click="addExtraGroup" style="width:100%;margin-right: 0;"
                                            class="primary-button">ADD A
                                        GROUP
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(window).on('group-added', event => {
                            $('[data-toggle="tooltip"]').tooltip('dispose');
                            $('[data-toggle="tooltip"]').tooltip({
                                html: true,
                                trigger: 'hover focus click'
                            });
                        });
                    </script>
                    <div class="extra-groups">
                        @foreach ($extraGroups as $groupIndex => $extraGroup)
                            <div wire:key="{{ $groupIndex + rand(10000, 20000) }}" class="bundle-group">
                                <div class="bundle-group__row bundle-group__row--details">
                                    <div class="bundle-group__cell">
                                        <div class="custom">
                                            <label for="bundle-extra-group-{{ $groupIndex }}-input-name">Group
                                                Name</label>
                                            <input type="text" class="group-input"
                                                   id="bundle-extra-group-{{ $groupIndex }}-input-name"
                                                   name="extra[{{ $groupIndex }}][groupname]"
                                                   value="{{ old('extra.' . $groupIndex . '.groupname') ?? $extraGroup['name'] }}"
                                                   style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="bundle-group__cell">
                                        <div class="custom">
                                            <label for="bundle-extra-group-{{ $groupIndex }}-input-min">
                                                Min
                                                <span class="bundle-group__tooltip icon-info" data-toggle="tooltip"
                                                      data-placement="top" data-html="true"
                                                      title='Minimum items a client must pick. Can be "0" if the choice is optional. <br><strong>Example:</strong> For optional items, set to "0". For required main dishes, set to "1".'></span>
                                            </label>
                                            <input type="text" id="bundle-extra-group-{{ $groupIndex }}-input-min"
                                                   name="extra[{{ $groupIndex }}][minlimit]"
                                                   value="{{ old('extra.' . $groupIndex . '.minlimit') ?? $extraGroup['min_limit'] }}"
                                                   style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="bundle-group__cell">
                                        <div class="custom">
                                            <label for="bundle-extra-group-{{ $groupIndex }}-input-max">
                                                Max
                                                <span class="icon-info" data-toggle="tooltip" data-placement="top"
                                                      data-html="true"
                                                      title='Maximum items a client can pick from a category. <br><strong>Example:</strong> For milk choices in a cappuccino, if only one type of milk can be selected, set to "1". If multiple toppings can be added to a pizza, set to the maximum number allowed.'></span>
                                            </label>
                                            <input type="text" id="bundle-extra-group-{{ $groupIndex }}-input-max"
                                                   name="extra[{{ $groupIndex }}][grouplimit]"
                                                   value="{{ old('extra.' . $groupIndex . '.grouplimit') ?? $extraGroup['limit'] }}"
                                                   style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="bundle-group__cell">
                                        <button type="button" class="btn delete-button extra-remove"
                                                wire:click="removeGroup('extra',{{ $groupIndex }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="bundle-group__cell bundle-group__cell--errors">
                                        @if (@$errorsHandling['extra.' . $groupIndex . '.groupname'])
                                            <div class="error">
                                                {{ $errorsHandling['extra.' . $groupIndex . '.groupname'][0] }}
                                            </div>
                                        @elseif (@$errorsHandling['extra.' . $groupIndex . '.minlimit'])
                                            <div class="error">
                                                {{ $errorsHandling['extra.' . $groupIndex . '.minlimit'][0] }}
                                            </div>
                                        @elseif (@$errorsHandling['extra.' . $groupIndex . '.grouplimit'])
                                            <div class="error">
                                                {{ $errorsHandling['extra.' . $groupIndex . '.grouplimit'][0] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="bundle-group__row">
                                    <div class="custom">
                                        <div style="display: flex;">
                                            <select wire:model.defer="extraSelected.{{ $groupIndex }}"
                                                    wire:key="{{ md5($groupIndex) }}" style="width: 100%" id="extra">
                                                @foreach ($extras as $relatedExtra)
                                                    <option value="{{ $relatedExtra->id }}">{{ $relatedExtra->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="button-container" style="margin-top:0!important;">
                                                <button type="button" wire:click="addExtra({{ $groupIndex }})"
                                                        style="margin-right: 0;height: 100%" class="primary-button">ADD
                                                    EXTRA
                                                </button>
                                            </div>
                                        </div>
                                        @if (@$errorsHandling['extra.' . $groupIndex . '.extras'])
                                            <div class="error">
                                                {{ $errorsHandling['extra.' . $groupIndex . '.extras'][0] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if (count($addedExtras[$groupIndex]) > 0)
                                    <div class="bundle-group__row bundle-group__row--extra">
                                        <div class="bundle-group__cell bundle-group__cell--header">Extra</div>
                                        <div class="bundle-group__cell bundle-group__cell--header">Order</div>
                                        <div class="bundle-group__cell bundle-group__cell--header">Price</div>
                                        <div class="bundle-group__cell bundle-group__cell--header"></div>
                                        @foreach ($addedExtras[$groupIndex] as $key => $addedExtra)
                                            <div class="bundle-group__cell">
                                                <div class="custom">
                                                    <input class="gray input-custom" value="{{ $addedExtra['name'] }}"
                                                           style="width: 100%;" disabled="">
                                                </div>
                                            </div>
                                            <div class="bundle-group__cell">
                                                <div class="custom">
                                                    <input class="gray input-custom" style="width: 100%;"
                                                           placeholder="Order"
                                                           value="{{ @$addedExtra['order'] ?? $loop->iteration * 5 }}"
                                                           name="extra[{{ $groupIndex }}][extras][{{ $loop->iteration }}][order]">
                                                </div>
                                            </div>
                                            <div class="bundle-group__cell">
                                                <div class="custom">
                                                    <input class="gray input-custom" style="width: 100%;"
                                                           placeholder="Price" value="{{ @$addedExtra['price'] }}"
                                                           name="extra[{{ $groupIndex }}][extras][{{ $loop->iteration }}][price]">
                                                </div>
                                            </div>
                                            <div class="bundle-group__cell">
                                                <input type="hidden"
                                                       name="extra[{{ $groupIndex }}][extras][{{ $loop->iteration }}][id]"
                                                       value="{{ $addedExtra['id'] }}" class="extraId">
                                                <button type="button" class="btn delete-button extra-remove"
                                                        wire:click="removeExtra({{ $addedExtra['id'] }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                         class="w-6 h-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="bundle-group__cell bundle-group__cell--errors">
                                                @if (@$errorsHandling['extra.' . $groupIndex . '.extras.' . $loop->iteration . '.order'])
                                                    <div class="error">
                                                        {{ $errorsHandling['extra.' . $groupIndex . '.extras.' . $loop->iteration . '.order'][0] }}
                                                    </div>
                                                @elseif (@$errorsHandling['extra.' . $groupIndex . '.extras.' . $loop->iteration . '.price'])
                                                    <div class="error">
                                                        {{ $errorsHandling['extra.' . $groupIndex . '.extras.' . $loop->iteration . '.price'][0] }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div id="products" class="tabcontent"
                     style="display: {{ $activeTab == 'products' ? 'block' : 'none' }}">
                    <div class="col-sm-12">
                        <div class="custom">
                            <div class="add-product-group-button" style="display: flex;gap: 6px;">
                                <div class="button-container" style="margin-top:0!important;width:100%">
                                    <button type="button" wire:click="addProductGroup"
                                            style="width:100%;margin-right: 0;" class="primary-button">ADD A GROUP
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product-groups">
                        @foreach ($productGroups as $groupIndex => $productGroup)
                            <div wire:key="{{ $groupIndex + rand(10000, 20000) }}" class="bundle-group">
                                <div class="bundle-group__row bundle-group__row--details">
                                    <div class="bundle-group__cell">
                                        <div class="custom">
                                            <label for="bundle-product-group-{{ $groupIndex }}-input-name">Group
                                                Name</label>
                                            <input type="text" class="group-input"
                                                   id="bundle-product-group-{{ $groupIndex }}-input-name"
                                                   name="product[{{ $groupIndex }}][groupname]"
                                                   value="{{ old('product.' . $groupIndex . '.groupname') ?? $productGroup['name'] }}"
                                                   style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="bundle-group__cell">
                                        <div class="custom">
                                            <label for="bundle-product-group-{{ $groupIndex }}-input-min">
                                                Min
                                                <span class="bundle-group__tooltip icon-info" data-toggle="tooltip"
                                                      data-placement="top" data-html="true"
                                                      title='Minimum items a client must pick. Can be "0" if the choice is optional. <br><strong>Example:</strong> For optional items, set to "0". For required main dishes, set to "1".'></span>
                                            </label>
                                            <input type="text" id="bundle-product-group-{{ $groupIndex }}-input-min"
                                                   name="product[{{ $groupIndex }}][minlimit]"
                                                   value="{{ old('product.' . $groupIndex . '.minlimit') ?? $productGroup['min_limit'] }}"
                                                   style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="bundle-group__cell">
                                        <div class="custom">
                                            <label for="bundle-product-group-{{ $groupIndex }}-input-max">
                                                Max
                                                <span class="icon-info" data-toggle="tooltip" data-placement="top"
                                                      data-html="true"
                                                      title='Maximum items a client can pick from a category. <br><strong>Example:</strong> For milk choices in a cappuccino, if only one type of milk can be selected, set to "1". If multiple toppings can be added to a pizza, set to the maximum number allowed.'></span>
                                            </label>
                                            <input type="text" id="bundle-product-group-{{ $groupIndex }}-input-max"
                                                   name="product[{{ $groupIndex }}][grouplimit]"
                                                   value="{{ old('product.' . $groupIndex . '.grouplimit') ?? $productGroup['limit'] }}"
                                                   style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="bundle-group__cell">
                                        <button type="button" class="btn delete-button extra-remove"
                                                wire:click="removeGroup('product',{{ $groupIndex }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="bundle-group__cell bundle-group__cell--errors">
                                        @if (@$errorsHandling['product.' . $groupIndex . '.groupname'])
                                            <div class="error">
                                                {{ $errorsHandling['product.' . $groupIndex . '.groupname'][0] }}
                                            </div>
                                        @elseif (@$errorsHandling['product.' . $groupIndex . '.grouplimit'])
                                            <div class="error">
                                                {{ $errorsHandling['product.' . $groupIndex . '.grouplimit'][0] }}
                                            </div>
                                        @elseif (@$errorsHandling['product.' . $groupIndex . '.minlimit'])
                                            <div class="error">
                                                {{ $errorsHandling['product.' . $groupIndex . '.minlimit'][0] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="bundle-group__row">
                                    <div class="custom">
                                        <div style="display: flex;">
                                            <select wire:model.defer="productSelected.{{ $groupIndex }}"
                                                    wire:key="{{ md5($groupIndex) }}" style="width: 100%" id="product">
                                                @foreach ($relatedProducts as $relatedProduct)
                                                    <option
                                                        value="{{ $relatedProduct->id }}">{{ $relatedProduct->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="button-container" style="margin-top:0!important;">
                                                <button type="button" wire:click="addProduct({{ $groupIndex }})"
                                                        style="margin-right: 0;height: 100%" class="primary-button">ADD
                                                    PRODUCT
                                                </button>
                                            </div>
                                        </div>
                                        @if (@$errorsHandling['product.' . $groupIndex . '.products'])
                                            <div class="error">
                                                {{ $errorsHandling['product.' . $groupIndex . '.products'][0] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if (count($addedProducts[$groupIndex]) > 0)
                                    <div class="bundle-group__row bundle-group__row--extra">
                                        <div class="bundle-group__cell bundle-group__cell--header">Extra</div>
                                        <div class="bundle-group__cell bundle-group__cell--header">Order</div>
                                        <div class="bundle-group__cell bundle-group__cell--header">Price</div>
                                        <div class="bundle-group__cell bundle-group__cell--header"></div>
                                        @foreach ($addedProducts[$groupIndex] as $key => $addedProduct)
                                            <div class="bundle-group__cell">
                                                <div class="custom">
                                                    <input class="gray input-custom" value="{{ $addedProduct['name'] }}"
                                                           style="width: 100%;" disabled="">
                                                </div>
                                            </div>
                                            <div class="bundle-group__cell">
                                                <div class="custom">
                                                    <input class="gray input-custom" style="width: 100%;"
                                                           placeholder="Order"
                                                           value="{{ @$addedProduct['order'] ?? $loop->iteration * 5 }}"
                                                           name="product[{{ $groupIndex }}][products][{{ $loop->iteration }}][order]">
                                                </div>
                                            </div>
                                            <div class="bundle-group__cell">
                                                <div class="custom">
                                                    <input class="gray input-custom" style="width: 100%;"
                                                           placeholder="Price" value="{{ @$addedProduct['price'] }}"
                                                           name="product[{{ $groupIndex }}][products][{{ $loop->iteration }}][price]">
                                                </div>
                                            </div>
                                            <div class="bundle-group__cell">
                                                <input type="hidden"
                                                       name="product[{{ $groupIndex }}][products][{{ $loop->iteration }}][id]"
                                                       value="{{ $addedProduct['id'] }}" class="productId">
                                                <button type="button" class="btn delete-button extra-remove"
                                                        wire:click="removeProduct({{ $addedProduct['id'] }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                         class="w-6 h-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="bundle-group__cell bundle-group__cell--errors">
                                                @if (@$errorsHandling['product.' . $groupIndex . '.products.' . $loop->iteration . '.order'])
                                                    <div class="error">
                                                        {{ $errorsHandling['product.' . $groupIndex . '.products.' . $loop->iteration . '.order'][0] }}
                                                    </div>
                                                @elseif (@$errorsHandling['product.' . $groupIndex . '.products.' . $loop->iteration . '.price'])
                                                    <div class="error">
                                                        {{ $errorsHandling['product.' . $groupIndex . '.products.' . $loop->iteration . '.price'][0] }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div id="removables" class="tabcontent"
                     style="display: {{ $activeTab == 'removables' ? 'block' : 'none' }};">
                    <div class="col-sm-12">
                        <div class="custom">
                            <div class="add-removable-group-button" style="display: flex;gap: 6px;">
                                <div class="button-container" style="margin-top:0!important;width:100%">
                                    <button type="button" wire:click="addRemovableGroup"
                                            style="width:100%;margin-right: 0;" class="primary-button">ADD A
                                        GROUP
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="removable-groups">
                        @foreach ($removableGroups as $groupIndex => $removableGroup)
                            <div wire:key="{{ $groupIndex + rand(10000, 20000) }}" class="col-sm-12">
                                <div class="custom">
                                    <div class="group-input-container">
                                        <div class="group-name">
                                            <input type="text" placeholder="Group name" class="group-input"
                                                   name="removable[{{ $groupIndex }}][groupname]"
                                                   value="{{ old('removable.' . $groupIndex . '.groupname') ?? $removableGroup['name'] }}"
                                                   style="width: 100%">
                                            @if (@$errorsHandling['removable.' . $groupIndex . '.groupname'])
                                                <div class="error">
                                                    {{ $errorsHandling['removable.' . $groupIndex . '.groupname'][0] }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="group-limit">
                                            <input type="text" placeholder="Selectable limit"
                                                   name="removable[{{ $groupIndex }}][grouplimit]"
                                                   value="{{ old('removable.' . $groupIndex . '.grouplimit') ?? $removableGroup['limit'] }}"
                                                   style="width: 100%">
                                            @if (@$errorsHandling['removable.' . $groupIndex . '.grouplimit'])
                                                <div class="error">
                                                    {{ $errorsHandling['removable.' . $groupIndex . '.grouplimit'][0] }}
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="removeGroup('removable',{{ $groupIndex }})"
                                                class="btn delete-button extra-remove">Remove Group
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="custom">
                                    <div style="display: flex;">
                                        <select wire:model.defer="removableSelected.{{ $groupIndex }}"
                                                wire:key="{{ md5($groupIndex) }}" style="width: 100%" id="removable">
                                            @foreach ($removables as $relatedRemovable)
                                                <option value="{{ $relatedRemovable->id }}">
                                                    {{ $relatedRemovable->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="button-container" style="margin-top:0!important;">
                                            <button type="button" wire:click="addRemovable({{ $groupIndex }})"
                                                    style="margin-right: 0;height: 100%" class="primary-button">ADD
                                                REMOVABLE
                                            </button>
                                        </div>
                                    </div>
                                    @if (@$errorsHandling['removable.' . $groupIndex . '.removables'])
                                        <div class="error">
                                            {{ $errorsHandling['removable.' . $groupIndex . '.removables'][0] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if (count($addedRemovables[$groupIndex]) > 0)
                                <div class="col-sm-12" style="margin-bottom: 4rem;">
                                    <div class="table-head">
                                        <div class="col-md-6">
                                            <p>Name</p>
                                        </div>
                                        <div class="col-md-5">
                                            <p>Display Order</p>
                                        </div>
                                        <div class="col-md-1"></div>
                                    </div>
                                    @foreach ($addedRemovables[$groupIndex] as $key => $addedRemovable)
                                        <div class="row custom child-div removable-div">
                                            <div class="col-md-6">
                                                <input class="gray input-custom" value="{{ $addedRemovable['name'] }}"
                                                       style="width: 100%;" disabled="">
                                            </div>
                                            <div class="col-md-5">
                                                <input class="gray input-custom" style="width: 100%;"
                                                       placeholder="Order"
                                                       value="{{ @$addedRemovable['order'] ?? $loop->iteration * 5 }}"
                                                       name="removable[{{ $groupIndex }}][removables][{{ $loop->iteration }}][order]">
                                                @if (@$errorsHandling['removable.' . $groupIndex . '.removables.' . $loop->iteration . '.order'])
                                                    <div class="error">
                                                        {{ $errorsHandling['removable.' . $groupIndex . '.removables.' . $loop->iteration . '.order'][0] }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-1">
                                                <input type="hidden"
                                                       name="removable[{{ $groupIndex }}][removables][{{ $loop->iteration }}][id]"
                                                       value="{{ $addedRemovable['id'] }}" class="removableId">
                                                <button type="button" class="btn delete-button extra-remove"
                                                        wire:click="removeRemovable({{ $addedRemovable['id'] }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                         class="w-6 h-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

