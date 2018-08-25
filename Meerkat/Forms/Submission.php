<?php

namespace Statamic\Addons\Meerkat\Forms;

use ArrayAccess;
use JsonSerializable;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Forms\Submission as StatamicSubmission;

class Submission extends StatamicSubmission implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{

    /**
     * The ID of the submission.
     *
     * @var string
     */
    protected $id;

    /**
     * The original data.
     *
     * @var array
     */
    protected $originalData = [];

    /**
     * The supplement data.
     *
     * @var array
     */
    protected $supplements = [];

    /**
     * List any attributes that you want to track the dirty state of here.
     *
     * @var array
     */
    protected $markAsDirty = [];

    /**
     * Contains the dirty state of 'watched' submission attributes.
     *
     * @var array
     */
    protected $watchedDirtyAttributes = [];

    /**
     * Indicates if the Submission is dirty.
     *
     * @var bool
     */
    protected $isDirty = false;

    /**
     * Gets a value from the original submission data.
     *
     * The original data might contain more information than
     * is defined within the submission's formset. Sneaky.
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    protected function original($key, $default = null)
    {
        return array_get($this->originalData, $key, $default);
    }

    /**
     * Get or set the ID
     *
     * @param mixed|null
     * @return mixed
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id ?: time();
        }

        $this->id = $id;
        
        return $id;
    }

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return array
     * @throws PublishException|HoneypotException
     */
    public function data($data = null)
    {
        if (!is_null($data)) {
            $this->originalData = $data;

            if (isset($this->originalData['id'])) {
                $this->id = $this->originalData['id'];
            }

        }

        return parent::data($data);
    }

    /**
     * Get an attribute from the submissions
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->originalData)) {
            return $this->originalData[$key];
        }

        return null;
    }

    public function getSupplement($key, $default = null)
    {
        return array_get($this->supplements, $key, $default);
    }

    /**
     * Convert the Submission into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the Submission to JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->originalData[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->originalData[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->originalData[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->originalData[$offset]);
    }

    /**
     * Saves the submission.
     *
     * This method should be overridden by child classes, but
     * we will update the dirty states here anyways to that
     * the dirty* methods always behave consistently.
     */
    public function save()
    {
        $this->watchedDirtyAttributes = [];
        $this->isDirty = false;
        parent::save();
    }


    /**
     * Dynamically set an attribute's value.
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->originalData[$key] = $value;

        // Indicate that some attribute is 'dirty'.
        if (in_array($key, $this->markAsDirty)) {
            $this->watchedDirtyAttributes[] = $key;
        }

        // Indicate that this Submission is dirty.
        $this->isDirty = true;
    }

    /**
     * Indicates whether a given attribute is 'dirty'.
     *
     * @param  $attribute
     * @return bool
     */
    public function hasDirtyAttribute($attribute)
    {
        return in_array($attribute, $this->watchedDirtyAttributes);
    }

    /**
     * Indicates if the Submission is dirty and needs to be saved.
     *
     * @return bool
     */
    public function isDirty()
    {
        return $this->isDirty;
    }

    public function getStoredData()
    {
        return array_merge($this->toArray(), $this->originalData);
    }

    /**
     * Dynamically handle calls to the Submission class.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'is') && Str::endsWith($method, 'Dirty')) {
            return $this->hasDirtyAttribute(mb_strtolower(mb_substr($method, 2, -5)));
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }

}
