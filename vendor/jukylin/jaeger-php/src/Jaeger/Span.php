<?php

namespace Jaeger;

use OpenTracing\SpanContext;
use OpenTracing\Reference;
use Jaeger\Thrift\SpanRefType;

class Span implements \OpenTracing\Span{

    private $operationName = '';

    public $startTime = '';

    public $finishTime = '';

    public $spanKind = '';

    public $spanContext = null;

    public $duration = 0;

    public $logs = [];

    public $tags = [];

    public $references = [];

    public function __construct($operationName, SpanContext $spanContext, $references){
        $this->operationName = $operationName;
        $this->startTime = $this->microtimeToInt();
        $this->spanContext = $spanContext;
        $this->references = $references;
    }

    /**
     * @return string
     */
    public function getOperationName(){
        return $this->operationName;
    }

    /**
     * @return SpanContext
     */
    public function getContext(){
        return $this->spanContext;
    }

    /**
     * @param float|int|\DateTimeInterface|null $finishTime if passing float or int
     * it should represent the timestamp (including as many decimal places as you need)
     * @param array $logRecords
     * @return mixed
     */
    public function finish($finishTime = null, array $logRecords = []){
        $this->finishTime = $finishTime == null ? $this->microtimeToInt() : $finishTime;
        $this->duration = $this->finishTime - $this->startTime;
    }

    /**
     * @param string $newOperationName
     */
    public function overwriteOperationName($newOperationName){
        $this->operationName = $newOperationName;
    }

    /**
     * Adds tags to the Span in key:value format, key must be a string and tag must be either
     * a string, a boolean value, or a numeric type.
     *
     * As an implementor, consider using "standard tags" listed in {@see \OpenTracing\Ext\Tags}
     *
     * @param array $tags
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function setTags(array $tags){
        $this->tags = array_merge($this->tags, $tags);
    }

    /**
     * Adds a log record to the span
     *
     * @param array $fields
     * @param int|float|\DateTimeInterface $timestamp
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function log(array $fields = [], $timestamp = null){
        $log['timestamp'] = $timestamp ? $timestamp : $this->microtimeToInt();
        $log['fields'] = $fields;
        $this->logs[] = $log;
    }

    /**
     * Adds a baggage item to the SpanContext which is immutable so it is required to use SpanContext::withBaggageItem
     * to get a new one.
     *
     * @param string $key
     * @param string $value
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function addBaggageItem($key, $value){
        $this->log([
            'event' => 'baggage',
            'key' => $key,
            'value' => $value,
        ]);
        return $this->spanContext->withBaggageItem($key, $value);
    }

    /**
     * @param string $key
     * @return string
     */
    public function getBaggageItem($key){
        $this->spanContext->getBaggageItem($key);
    }


    private function microtimeToInt(){
        return intval(microtime(true) * 1000000);
    }
}