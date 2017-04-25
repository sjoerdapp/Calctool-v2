<?php

namespace BynqIO\CalculatieTool\Models;

use BynqIO\CalculatieTool\Models\Contact;
use BynqIO\CalculatieTool\Models\Traits\Ownable;
use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    use Ownable;

    protected $table = 'relation';
    protected $guarded = array('id', 'debtor_code');

    // public function user() {
    //     return $this->hasOne('User');
    // }

    // public function province() {
    //     return $this->hasOne('Province');
    // }

    public function contacts() {
        return $this->hasMany(Contact::class);
    }

    // public function country() {
    //     return $this->hasOne('Country');
    // }

    // public function resource() {
    //     return $this->hasOne('Resource');
    // }

    // public function type() {
    //     return $this->hasOne('RelationType');
    // }

    public function kind() {
        return $this->hasOne('BynqIO\CalculatieTool\Models\RelationKind', 'id', 'kind_id')->first();
    }

    public function isActive() {
        return $this->active;
    }

    public function isBusiness() {
        return $this->kind()->kind_name == 'zakelijk';
    }

    public function name() {
        if ($this->isBusiness()) {
            return $this->company_name;
        }

        return Contact::where('relation_id','=',$this->id)->first()['firstname'] . ' ' . Contact::where('relation_id','=',$this->id)->first()['lastname'];
    }
}
