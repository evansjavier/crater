<?php

namespace Crater\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueNumber implements Rule
{
    public $id;
    public $class;
    public $company_id;

    /**
     * Create a new rule instance.
     * @param  string  $class
     * @param  int  $id
     * @return void
     */
    public function __construct(string $class = null, int $id = null, $company_id = null)
    {
        $this->class = $class;
        $this->id = $id;
        $this->company_id = $company_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value && count(explode("-", $value)) > 2) {
            $number = explode("-",$value);
            $uniqueNumber = $number[0].'-'.sprintf('%06d', intval($number[1]));
        } else {
            $uniqueNumber = $value;
        }

        if($this->company_id){
            if ($this->id && $this->class::where('id', $this->id)->where($attribute, $uniqueNumber)->where('company_id', $this->company_id)->first()) {
                return true;
            }

            if ($this->class::where($attribute, $uniqueNumber)->where('company_id', $this->company_id)->first()) {
                return false;
            }
        }
        else{
            if ($this->id && $this->class::where('id', $this->id)->where($attribute, $uniqueNumber)->first()) {
                return true;
            }

            if ($this->class::where($attribute, $uniqueNumber)->first()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid number passed.';
    }
}
