<?php

namespace App\Queries;

use Codeception\Specify;
use LaravelCommon\App\Database\Eloquent\Relations\HasManyRelation;
use Prophecy\PhpUnit\ProphecyTrait;
use LaravelCommon\Tests\UnitTest;
use LaravelCommon\App\Database\Eloquent\Relations\Models\Item;
use LaravelCommon\App\Database\Eloquent\Relations\Models\ItemStock;

class HasManyRelationUnitTest extends UnitTest
{
    use Specify;
    use ProphecyTrait;

    protected HasManyRelation $hasManyRelation;

    public function test()
    {
        $this->beforeSpecify(function () {
            $this->hasManyRelation = new HasManyRelation(
                new Item(),
                ItemStock::class,
                'item_id'
            );

            $this->itemStock = (new ItemStock());
        });

        $this->describe('->add()', function () {
            $this->describe('when added model has no key / not persisted', function () {
                $this->describe('when model is not removed', function () {
                    $result = $this->hasManyRelation->add($this->itemStock);
                    verify($result->getAddedModelCollection()->count())->equals(1);
                    verify($result->getRemovedModelCollection()->count())->equals(0);
                });

                $this->describe('when model is removed before', function () {
                    $this->it('should remove from removed collection', function () {
                        $this->hasManyRelation->getRemovedModelCollection()->add($this->itemStock);
                        verify($this->hasManyRelation->getRemovedModelCollection()->count())->equals(1);
                        $result = $this->hasManyRelation->add($this->itemStock);
                        verify($result->getAddedModelCollection()->count())->equals(1);
                        verify($result->getRemovedModelCollection()->count())->equals(0);
                    });
                });

                $this->describe('when model is already added', function () {
                    $this->it('should not add the same instance', function () {
                        $result = $this->hasManyRelation->add($this->itemStock);
                        $result = $this->hasManyRelation->add($this->itemStock);
                        verify($result->getAddedModelCollection()->count())->equals(1);
                    });
                });
            });

            $this->describe('when added model has key / persisted', function () {
                $this->describe('when model is not removed', function () {
                    $this->itemStock->setId(1);
                    $result = $this->hasManyRelation->add($this->itemStock);
                    verify($result->getAddedModelCollection()->count())->equals(1);
                    verify($result->getRemovedModelCollection()->count())->equals(0);
                });

                $this->describe('when model is removed before', function () {
                    $this->it('should remove from removed collection', function () {
                        $this->itemStock->setId(1);
                        $this->hasManyRelation->getRemovedModelCollection()->add($this->itemStock);
                        verify($this->hasManyRelation->getRemovedModelCollection()->count())->equals(1);
                        $result = $this->hasManyRelation->add($this->itemStock);
                        verify($result->getAddedModelCollection()->count())->equals(1);
                        verify($result->getRemovedModelCollection()->count())->equals(0);
                    });
                });

                $this->describe('when model is already added but not related', function () {
                    $this->it('should not add the same object with same ID', function () {
                        $this->itemStock->setId(1);
                        $result = $this->hasManyRelation->add($this->itemStock);
                        $result = $this->hasManyRelation->add($this->itemStock);
                        verify($result->getAddedModelCollection()->count())->equals(1);
                    });

                    $this->describe('when add model more than 1', function () {
                        verify($this->hasManyRelation->get()->count())->equals(0);
                        $itemStock2 = (new ItemStock())
                            ->setId(2);
                        $this->itemStock->setId(1);
                        
                        $result = $this->hasManyRelation->add($this->itemStock);
                        $result = $this->hasManyRelation->add($itemStock2);
                        verify($result->get()->count())->equals(2);
                    });
                });

                $this->describe('when existing already has 1 data', function () {
                    $this->describe('when add model is already related from database', function () {
                        $this->it('should not add the same object with same ID', function () {
                            $this->itemStock->setId(1);
                            $this->hasManyRelation->get()->add($this->itemStock);
                            $result = $this->hasManyRelation->add($this->itemStock);
                            verify($this->hasManyRelation->get()->count())->equals(1);
                            verify($result->getAddedModelCollection()->count())->equals(0);
                        });
                    });

                    $this->describe('when add model that does not exist', function () {
                        $this->it('should add the different object with different ID', function () {
                            $this->itemStock->setId(1);
                            $this->hasManyRelation->get()->add($this->itemStock);
                            verify($this->hasManyRelation->get()->count())->equals(1);

                            $newItemStock = new ItemStock();
                            $newItemStock->setId(2);
                            $result = $this->hasManyRelation->add($newItemStock);
                            verify($result->getAddedModelCollection()->count())->equals(1);
                            verify($this->hasManyRelation->get()->count())->equals(2);
                        });
                    });
                });
            });
        });


        $this->describe('->remove()', function () {
            $this->describe('when added model has no key / not persisted', function () {
                $this->describe('when model is already added', function () {
                    $this->hasManyRelation->getAddedModelCollection()->add($this->itemStock);
                    verify($this->hasManyRelation->getAddedModelCollection()->count())->equals(1);

                    $result = $this->hasManyRelation->remove($this->itemStock);
                    verify($result->getAddedModelCollection()->count())->equals(0);
                    // remove non-persisted model should not be collected, we cant remove non persisted from database
                    // so just remove from added collection id any
                    verify($result->getRemovedModelCollection()->count())->equals(0);
                });
            });

            $this->describe('when added model has key / persisted', function () {
                $this->describe('when model does not exist in collection in database', function () {
                    $this->describe('when model is already added', function () {
                        $this->itemStock->setId(1);
                        $this->hasManyRelation->getAddedModelCollection()->add($this->itemStock);
                        verify($this->hasManyRelation->getAddedModelCollection()->count())->equals(1);

                        $result = $this->hasManyRelation->remove($this->itemStock);
                        verify($result->getAddedModelCollection()->count())->equals(0);
                        verify($result->getRemovedModelCollection()->count())->equals(0);
                    });
                });
            });

            $this->describe('when added model has key / persisted', function () {
                $this->describe('when model exists in collection in database', function () {
                    $this->describe('when model is not already in removed collection', function () {
                        $this->itemStock->setId(1);
                        $this->hasManyRelation->get()->add($this->itemStock);

                        $result = $this->hasManyRelation->remove($this->itemStock);
                        verify($result->getAddedModelCollection()->count())->equals(0);
                        verify($result->getRemovedModelCollection()->count())->equals(1);
                    });

                    $this->describe('when model is already in removed collection', function () {
                        $this->itemStock->setId(1);
                        $this->hasManyRelation->getRemovedModelCollection()->add($this->itemStock);
                        verify($this->hasManyRelation->getRemovedModelCollection()->count())->equals(1);

                        $result = $this->hasManyRelation->remove($this->itemStock);
                        verify($result->getAddedModelCollection()->count())->equals(0);
                        verify($result->getRemovedModelCollection()->count())->equals(1);
                    });
                });
            });
        });
    }
}
