<?php
/**
 * @file pda/paginator.inc.php
 * @brief 
 */
/**
 * @class PDA_Paginator
 * @brief Клас реализовывающий функцию пагинации
 *
 */
class PDA_Paginator implements Iterator
{
   private $ary;
   private $position;

   function __construct($ary, $offset, $detail_uri_base, $conf, $limit=10, $offset_name='off') {
      if ( empty($ary) || !is_array($ary) )
         throw new Exception('empty input $ary');

      if ( empty($limit) || empty($detail_uri_base) || empty($conf) )
         throw new Exception('0 or empty $limit or $detail_uri_base or $conf');

      $this->elems_all_nb = count($ary);
      $this->elems_per_page = (int)$limit;
      $this->offset_name = $offset_name;
      $this->cur_offset = $offset;
      $this->conf =& $conf;
      $this->uri_base = $detail_uri_base;

      $this->all_pages_nb = ceil($this->elems_all_nb / $this->elems_per_page);
      $this->cur_page = ( $this->cur_offset / $this->elems_per_page) + 1;
      $this->cur_page_end = $this->cur_offset + $this->elems_per_page;

      $this->position = $this->cur_offset;
      if ( $this->position > $this->elems_all_nb )
         throw new Exception('offset > count(array)');
      $this->ary =& $ary;
   }

   function print_above()
   {
      if ( $this->cur_page <= 1)
         return;
      printf("<div style='text-align: center; width:%upx;'>\n", $this->conf['pda-thumb-image-width']);
      $scale = isset($_GET['scl'])? $_GET['scl']:0;
      $FS_size ='';
      if(isset($_GET['aw']) && isset($_GET['ah'])){
      	$FS_size=sprintf('&aw=%s&ah=%s', $_GET['aw'], $_GET['ah']);
      }
      
      $uri =  sprintf('%s&%s=%u&scl=%u%s', $this->uri_base,
         $this->offset_name, ($this->elems_per_page * ($this->cur_page - 2)), $scale, $FS_size);

      print "<a href='$uri' title='пред.'>\n";
      printf("<img src='%s/img/arrow_up_48x24.png' width=48 height=24 alt='пред.'></a></div>\n",
         $this->conf['prefix']);
   }

   function print_below() {
   	  $scale = isset($_GET['scl'])? $_GET['scl']:0;
   	  $FS_size ='';
   	  if(isset($_GET['aw']) && isset($_GET['ah'])){
   	  	$FS_size=sprintf('&aw=%s&ah=%s', $_GET['aw'], $_GET['ah']);
   	  }
   	  
      if ( $this->cur_page < $this->all_pages_nb ) {
         printf("<div style='text-align: center; width:%upx;'>\n", $this->conf['pda-thumb-image-width']);
         $uri =  sprintf('%s&%s=%u&scl=%u%s', $this->uri_base,
            $this->offset_name, ($this->elems_per_page * $this->cur_page), $scale, $FS_size );

         print "<a href='$uri' title='след.'>\n";
         printf("<img src='%s/img/arrow_down_48x24.png' width=48 height=24 alt='след.'></a></div>\n",
            $this->conf['prefix']);
      }

      if ( $this->all_pages_nb > 1 ) {
         print "<div>Страницы: ";
         for ($p=1; $p<=$this->all_pages_nb; $p++) {
            if ($p === $this->cur_page)
               print "<span style='font-size: 140%';>$p</span>&nbsp;";
            else {
               $uri =  sprintf('%s&%s=%u&scl=%u%s', $this->uri_base,
                  $this->offset_name, ($this->elems_per_page * ($p - 1)), $scale, $FS_size);
               print "<a href='$uri' title='след.'>$p</a>&nbsp\n";
            }
         }
      }
      print "</div>\n";
   }

   function rewind() {
      $this->position = $this->cur_offset;
   }

   function current() {
      return $this->ary[$this->position];
   }

   function key() {
      return $this->position;
   }

   function next() {
      ++$this->position;
   }

   function valid() {
      if ( $this->position < $this->cur_page_end )
         return isset($this->ary[$this->position]);
      else
         return false;
   }
} // class Paginator

?>
