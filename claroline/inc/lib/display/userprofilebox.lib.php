<?php // $Id: userprofilebox.lib.php 12924 2011-03-03 14:45:00Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

FromKernel::uses('user.lib');

/**
* CLAROLINE
*
* User account summary
*
* @version      1.9 $Revision: 12924 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

class UserProfileBox implements Display
{
    protected $condensedMode;
    protected $userId;
    
    
    public function __construct( $condensedMode = false )
    {
        $this->condensedMode = $condensedMode;
        $this->userId = claro_get_current_user_id();
    }
    
    
    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }
    
    
    /**
     * Render content
     */
    public function render()
    {
        CssLoader::getInstance()->load( 'profile', 'all' );
        load_kernel_config('user_profile');
        
        $userData = user_get_properties( $this->userId );
        
        $pictureUrl = '';
        if ( get_conf('allow_profile_picture') )
        {
            $picturePath = user_get_picture_path( $userData );
            
            if ( $picturePath && file_exists( $picturePath ) )
            {
                $pictureUrl = user_get_picture_url( $userData );
            }
            else
            {
                $pictureUrl = get_icon_url('nopicture');
            }
        }
        
        $userFullName = htmlspecialchars(
            get_lang('%firstName %lastName',
                array('%firstName' => $userData['firstname'],
                      '%lastName' => $userData['lastname'])
            )
        );
        
        $dock = new ClaroDock('userProfileBox');
        
        $template = new CoreTemplate('user_profilebox.tpl.php');
        $template->assign('pictureUrl', $pictureUrl);
        $template->assign('userFullName', $userFullName);
        $template->assign('dock', $dock);
        $template->assign('condensedMode', $this->condensedMode);
        $template->assign('userData', $userData);
        
        return $template->render();
    }
}