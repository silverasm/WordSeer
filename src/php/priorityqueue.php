<?php
/*************
The MIT License
Copyright (c) 2010 Richard Ramsden
Permission is hereby granted, free of charge, to any person obtaining
 a copy of this software and associated documentation files (the 
“Software”), to deal in the Software without restriction, including 
without limitation the rights to use, copy, modify, merge, publish, 
distribute, sublicense, and/or sell copies of the Software, and to 
permit persons to whom the Software is furnished to do so, subject to 
the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, 
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY 
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, 
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*************/

class PriorityQueue
{
    /**
     * Min Heap
     *
     * [2*n + 1] - first child of [n]
     * [2*n + 2] - second child of [n]
     */
    var $heap = array();
	var $priorities = array();
	
     function push($element, $priority) {
	  $this->priorities[$element] = $priority;
      $count = count($this->heap);
      $this->heap[$count] = $element;
      $this->bubbleUp($count);
    }

     function IsEmpty() {
      return (count($this->heap) == 0) ? true : false;
    }

     function decreaseKey($i) {
      $this->heap[$i] = 9999;
      $this->bubbleDown($i);
    }

     function bubbleUp($i) {
      $parent = floor(($i-1)/2);
      if(!$this->less($this->heap[$i], $this->heap[$parent]) and ($parent >= 0)) {
        $this->swap($i, $parent);
        $this->bubbleUp($parent);
      }
    }

     function bubbleDown($i) {
      $left = 2*$i+1;
      $right = 2*$i+2;

      // check left subtree
      if($this->less($this->heap[$i], $this->heap[$left]) and $left < count($this->heap)) {
        $this->swap($i, $left);
        $this->bubbleDown($left);
      }

      // check right subtree
      if($this->less($this->heap[$i], $this->heap[$right]) and $right < count($this->heap)) {
        $this->swap($i, $right);
        $this->bubbleDown($right);
      }
    }

     function swap($x, $y) {
      $tmp = $this->heap[$x];
      $this->heap[$x] = $this->heap[$y];
      $this->heap[$y] = $tmp;
    }

     function top() {
      if (count($this->heap) == 0) return null;
      return $this->heap[0];
    }

     function pop() {
      $tmp = $this->heap[0];
      $this->swap(0, count($this->heap)-1);
      unset($this->heap[count($this->heap)-1]);
      $this->bubbleDown(0);
      return $tmp;
    }

     function clear() {
      $this->heap = array();
	  $this->priorities=array();
    }

     function less($ele1, $ele2) {
		if(array_key_exists($ele1, $this->priorities) && array_key_exists($ele2, $this->priorities)){
      return ($this->priorities[$ele1] < $this->priorities[$ele2]) ? true : false;
		}else{
			return false;
		}
    }
}

?>