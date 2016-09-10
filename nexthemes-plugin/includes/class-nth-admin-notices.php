<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

class Nexthemes_Admin_Notices {
	
	public function __construct(){
		
	}
	private static $msg;
	
	private static $type, $class = array();
	
	public static function create( $msg, $type = 'updated' ){
		self::$msg = $msg;
		self::$type = $type;
		switch( $type ){
			case "updated":
				self::$class[] = $type;
				break;
			case "warning":
				self::$class[] = "updated";
				self::$class[] = $type;
				break;
			default:
				self::$class = $type;
		}
		add_action( 'admin_notices', array( __CLASS__, 'notices_call' ) );
	}
	
	public static function notices_call(){
		self::$class[] = 'nth-notices';
		?>
		<div class="<?php echo esc_attr( implode( ' ', self::$class ) );?>">
			<p><?php echo self::$msg?></p>
		</div>
		<?php 
	}
	
}