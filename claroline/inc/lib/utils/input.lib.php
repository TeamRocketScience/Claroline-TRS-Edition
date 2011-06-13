<?php // $Id: input.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * User input library
 * Replacement for $_GET and $_POST
 * Do not handle $_COOKIES !
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     utils
 */
 
FromKernel::uses ( 'utils/validator.lib' );

/**
 * Data Input Exception, thrown when an input value does not match
 * a filter or is missing
 */
class Claro_Input_Exception extends Exception{};

/**
 * Defines the required methods for a data input object
 */
interface Claro_Input
{
    /**
     * Get a value given its name
     * @param   string $name variable name
     * @param   mixed $default default value (if $name is missingin the input)
     * @return  mixed value of $name in input data or $default value
     * @throws  Claro_Input_Exception on failure
     */
    public function get( $name, $default = null );
    /**
     * Get a value given its name, the value must be set in the data
     * but can be empty
     * @param   string $name variable name
     * @return  mixed value of $name
     * @throws  Claro_Input_Exception on failure or if $name is missing
     */
    public function getMandatory( $name );
}

/**
 * Array based data input class
 */
class Claro_Input_Array implements Claro_Input
{
    protected $input;
    protected $_notSet;
    
    /**
     * @param   array $input
     */
    public function __construct( $input )
    {
        $this->input = $input;
        // create a singleton object for the getMandatory method
        // this object will be used to check if a value is defined
        // in the input data in order to avoid pitfalls with the empty()
        // PHP function
        $this->_notSet = (object) null;
    }
    
    /**
     * @see     Claro_Input
     */
    public function get( $name, $default = null )
    {
        if ( array_key_exists( $name, $this->input ) )
        {
            return $this->input[$name];
        }
        else
        {
            return $default;
        }
    }
    
    /**
     * @see     Claro_Input
     */
    public function getMandatory( $name )
    {
        // get the value of the requested variable and give the _notSet
        // singleton object as the default value so we can check if the
        // varaible was set without having issues with the empty() function
        $ret = $this->get( $name, $this->_notSet );
        
        // check if $ret is the instance of the _notSet singleton object
        // if it is the case, the requested variable has not been set
        // in the input data so we have to throw an exception
        if ( $ret === $this->_notSet )
        {
            throw new Claro_Input_Exception(
                "{$name} not found in ".get_class($this)." !" );
        }
        else
        {
            return $ret;
        }
    }
}

/**
 * Data input class with filters callback for validation
 */
class Claro_Input_Validator implements Claro_Input
{
    protected $validators;
    protected $validatorsForAll;
    protected $input;
    
    /**
     * @param   Claro_Input $input
     */
    public function __construct( Claro_Input $input )
    {
        $this->validators = array();
        $this->validatorsForAll = array();
        $this->input = $input;
    }
    
    /**
     * Set a validator for the given variable
     * @param   string $name variable name
     * @param   Claro_Validator $validator validator object
     * @throws  Claro_Input_Exception if the filter callback is not callable
     */
    public function setValidator( $name, Claro_Validator $validator )
    {
        if ( ! array_key_exists( $name, $this->validators ) )
        {
            $this->validators[$name] = array();
        }
        
        $validatorCallback = array( $validator, 'isValid' );
        
        if ( ! is_callable( $validatorCallback ) )
        {
            throw new Claro_Input_Exception ("Invalid validator callback : " 
                . $this->getFilterCallbackString($validatorCallback));
        }
        
        $this->validators[$name][] = $validatorCallback;
    }
    
    /**
     * Set a validator for all variables
     * @param   string $name variable name
     * @param   Claro_Validator $validator validator object
     * @throws  Claro_Input_Exception if the filter callback is not callable
     */
    public function setValidatorForAll( Claro_Validator $validator )
    {
        $validatorCallback = array( $validator, 'isValid' );
        
        if ( ! is_callable( $validatorCallback ) )
        {
            throw new Claro_Input_Exception ("Invalid validator callback : " 
                . $this->getFilterCallbackString($validatorCallback));
        }
        
        $this->validatorsForAll[] = $validatorCallback;
    }
    
    /**
     * @see     Claro_Input
     * @throws  Claro_Input_Exception if $value does not pass the validator
     */
    public function get( $name, $default = null )
    {
        $tainted = $this->input->get( $name, $default );
        
        if ( ( is_null( $default ) && is_null( $tainted ) )
            || $tainted == $default )
        {
            return $default;
        }
        else
        {
            return $this->validate( $name, $tainted );
        }
    }
    
    /**
     * @see     Claro_Input
     * @throws  Claro_Input_Exception if $value does not pass the validator
     */
    public function getMandatory( $name )
    {
        $tainted = $this->input->getMandatory( $name );
        
        return $this->validate( $name, $tainted );
    }
    
    /**
     * @param   string $name
     * @param   mixed $tainted value
     * @throws  Claro_Validator_Exception if $value does not pass the
     * filter for $name
     */
    public function validate( $name, $tainted )
    {
        // validators for all variables if any
        if ( !empty ($this->validatorsForAll ) )
        {
            foreach ( $this->validatorsForAll as $validatorForAllCallback )
            {
                if ( ! call_user_func( $validatorForAllCallback, $tainted ) )
                {
                    throw new Claro_Validator_Exception(
                        get_class( $validatorForAllCallback[0] )
                        . " : {$name} does not pass the validator !" );
                }
            }
        }
        
        // validators for the requested variable
        if ( array_key_exists( $name, $this->validators ) )
        {
            foreach ( $this->validators[$name] as $validatorCallback )
            {
                if ( ! call_user_func( $validatorCallback, $tainted ) )
                {
                    throw new Claro_Validator_Exception(
                        get_class( $validatorCallback[0] )
                        . " : {$name} does not pass the validator !" );
                }
            }
        }
        
        return $tainted;
    }
}

/**
 * User input class to replace $_REQUEST
 */
class Claro_UserInput
{        
    protected static $instance = false;
    
    /**
     * Get user input object
     * @return  Claro_Input_Validator
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            // Create an input validator instance using the $_GET
            // and $_POST super arrays
            self::$instance = new Claro_Input_Validator( 
                new Claro_Input_Array( array_merge( $_GET, $_POST ) ) );
            
            self::$instance->setValidatorForAll( new Claro_Validator_CustomNotEmpty() );
        }
        
        return self::$instance;
    }
}
