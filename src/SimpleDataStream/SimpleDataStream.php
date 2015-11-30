<?php

namespace SimpleDataStream;

class SimpleDataStream
{
	private $generator;
	private $callbacks = array();
	private $queue;

	public function __construct(){
		$this->generator = $this->getGenerator();
		$this->queue = new \SplQueue();
	}

	public function put($v){
		$this->generator->send($v);
	}

	public function filter(callable $fn){
		$this->addCallback('filter', $fn);
		return $this;
	}

	public function map(callable $fn){
		$tmp = new \ReflectionFunction($fn);
		$params = $tmp->getParameters();
		$this->addCallback('map', $fn, $params[0]->isPassedByReference());
		return $this;
	}

	public function __invoke($num = null){
		$buffer = array();
		while(null !== $v = $this->get($num, count($buffer))){
			if(empty($num)){
				return $v;
			}
			else {
				$buffer[] = $v;
			}
		}
		return $buffer;
	}

	public function all(){
		return $this(count($this->queue));
	}

	private function get($num, $count){
		if(isset($num) && ($num == $count)){
			return null;
		}
		if(count($this->queue) === 0){
			return null;
		}
		return $this->queue->dequeue();
	}


	private function getGenerator(){
		while(true){
			begin:
			$v = yield;
			foreach($this->callbacks as $callback){
				switch($callback[0]){
					case 'map':
						if($callback[2]){
							$callback[1]($v);
						}
						else{
							$v = $callback[1]($v);
						}
						break;
					case 'filter':
						if(!$callback[1]($v)){
							goto begin;
						}
						break;
				}
			}
			$this->queue->enqueue($v);
		};
	}

	private function addCallback($type, callable $fn, $returnsReference = false){
		$this->callbacks[] = array($type, $fn, $returnsReference);
	}

}
