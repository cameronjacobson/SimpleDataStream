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
		$this->addCallback('filter',$fn);
		return $this;
	}

	public function map(callable $fn){
		$this->addCallback('map',$fn);
		return $this;
	}

	public function __invoke($num = null){
		if(count($this->queue) === 0){
			return array();
		}
		$buffer = array();
		while(null !== $v = $this->get()){
			$buffer[] = $v;
			if($num && (count($buffer) == $num)){
				return $buffer;
			}
		}
		return $buffer;
	}

	public function get(){
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
						$callback[1]($v);
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

	private function addCallback($type,callable $fn){
		$this->callbacks[] = array($type,$fn);
	}

}
