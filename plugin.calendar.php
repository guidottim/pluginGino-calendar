<?php
/**
 * @mainpage Applicazione per la gestione dei calendari (eventi, appuntamenti, ...)
 * 
 * INSTALLAZIONE
 * ---------------
 * 1. Scaricare la libreria
 * 2. Copiare il file plugin.calendar.php nella directory /lib/plugin.
 * 3. Copiare i file calendar.php, calendar_month.php, calendar_month_day.php, user_calendar.php nella directory /views oppure nella directory views dell'applicazione che richiama calendar.
 * 4. Copiare il file calendar.css nella directory dell'applicazione che richiama calendar.
 * 
 * UTILIZZO
 * ---------------
 * Per attivare la libreria occorre includerla all'inizio del file:
 * @code
 * require_once(PLUGIN_DIR.OS.'plugin.calendar.php');
 * @endcode
 * 
 * ESEMPIO
 * ---------------
 * 
 * @code
 * public function calendar() {
 *   $registry = registry::instance();
 *   $registry->addCss($this->_class_www."/example.css");
 *   $registry->addCss($this->_class_www.'/calendar.css');
 *   
 *   $calendar = new calendarExample($this->_instanceName, 'calendar');
 *   $buffer = $calendar->printCalendar(array(
 *     'title'=>null, 
 *     'return_link'=>$this->_plink->aLink($this->_instanceName, 'calendar', $params), 
 *     'ajax_url'=>$this->_home."?pt[$this->_instanceName-choice]"
 *   ));
 *   return $buffer;
 * }
 * @endcode
 * La classe calendarExample() estende la classe calendar e sovrascrive il metodo calendarDay()
 * @code
 * class calendarExample extends calendar {
 *   protected function calendarDay($date, $options) {
 *     // contenuti
 *   }
 * }
 * @endcode
 */

/**
 * @file plugin.calendar.php
 * @brief Contiene la classe plugin_calendar
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Classe per la gestione dei calendari
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class plugin_calendar {
		
	/**
	 * Nome dell'istanza
	 * 
	 * @var string
	 */
	protected $_instanceName;
	
	/**
	 * Nome del metodo che istanzia la classe calendar
	 * 
	 * @var string
	 */
	protected $_method;
	
	/**
	 * Costruttore
	 * 
	 * @param string $instance nome dell'istanza per la definizione del link di scorrimento
	 * @param string $method nome del metodo per la definizione del link di scorrimento
	 */
	function __construct($instance, $method) {
		
		$this->_instanceName = $instance;
		$this->_method = $method;
	}
	
	/**
	 * Stampa il calendario
	 * 
	 * @param array $options
	 *   array associativo di opzioni + opzioni del metodo calendarDay()
	 *   - @b title (string): titolo
	 *   - @b add_params (array): parametri da aggiungere ai link precedente/successivo, nel formato array(nome_param=>valore_param[,])
	 * @return string
	 */
	public function printCalendar($options) {

		$title = array_key_exists('title', $options) ? $options['title'] : ucfirst(_("Calendario"));
		$add_params = (array_key_exists('add_params', $options) && is_array($options['add_params'])) ? $options['add_params'] : array();
		
		// get month/year to display
		$year = cleanVar($_GET, 'year', 'int', '');
		$month_num = cleanVar($_GET, 'month', 'int', '');

		if(!$year) $year = date("Y");
		if(!$month_num) $month_num = date("n");
		// end get month/year to display
		
		// todo read from get
		$first_day_month = new Datetime($year.'-'.($month_num < 9 ? '0' : '').$month_num.'-01');

		// month view
		$days = array(); // days of the month
		$month_label = $first_day_month->format('F').' '.$year;

		$first_day_of_week = $first_day_month->format('N');		
		$days[$first_day_of_week - 1] = $this->calendarDay($first_day_month, $options);
		$first_day_month->modify('+1 day');
		
		while($first_day_month->format('n') == $month_num) {
			
			$days[] = $this->calendarDay($first_day_month, $options);
			$first_day_month->modify('+1 day');
		}

		// prev/next month controllers
		$prev_month_y = $year;
		$next_month_y = $prev_month_y;
		$prev_month = $month_num - 1;
		$next_month = $month_num + 1;
		if($month_num == 12) {
			$next_month_y = $year + 1;
			$next_month = 1;
		}
		elseif($month_num == 1) {
			$prev_month_y = $year - 1;
			$prev_month = 12;
		}
		// end prev/next month controllers

		$plink = new Link();
		
		$view = new view();
		$view->setViewTpl('calendar_month');
		$view->assign('prev_month_link', "<a href=\"".$plink->aLink($this->_instanceName, $this->_method, array_merge($add_params, array('year'=>$prev_month_y, 'month'=>$prev_month)))."\">"._("precedente")."</a>");
		$view->assign('next_month_link', "<a href=\"".$plink->aLink($this->_instanceName, $this->_method, array_merge($add_params, array('year'=>$next_month_y, 'month'=>$next_month)))."\">"._("successivo")."</a>");
		$view->assign('month_label', $month_label);
		$view->assign('days', $days);
		$table_month = $view->render();
		// end month view
		
		$view = new view();
		$view->setViewTpl('calendar');
		$view->assign('table_month', $table_month);
		$calendar = $view->render();
		
		$view = new view();
		$view->setViewTpl('user_calendar');
		$view->assign('title', $title);
		$view->assign('calendar', $calendar);
		return $view->render();
	}
	
	/**
	 * Contenuti di una singola giornata
	 * 
	 * @param object $date
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b return_link (string): indirizzo di ritorno in seguito ad una azione
	 * @return string
	 */
	protected function calendarDay($date, $options) {

		$return_link = array_key_exists('return_link', $options) ? $options['return_link'] : $_SERVER['REQUEST_URI'];
		
		$link_view_day = null;
		$link_insert_day = null;
		$items = array();
		
		$date_string = $date->format('Y-m-d');
		
		$view = new view();
		
		$view->setViewTpl('calendar_month_day');
		$view->assign('day_num', $date->format('d'));
		$view->assign('today', ($date->format('Y-m-d') == date('Y-m-d') ? true : false));
		$view->assign('reservation_items', $items);
		$view->assign('link_view_day', $link_view_day);
		$view->assign('link_insert_day', $link_insert_day);
		
		return null;
	}
}
?>