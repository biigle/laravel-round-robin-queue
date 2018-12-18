<?php

namespace Biigle\RoundRobinQueue\Tests;

use Queue;
use Mockery;
use Biigle\RoundRobinQueue\RoundRobinQueue;

class RoundRobinQueueTest extends TestCase
{
   public function setUp()
   {
      parent::setUp();
      config(['queue.connections' => [
         'rr' => [
            'driver' => 'roundrobin',
            'queue' => 'default',
            'connections' => ['q1', 'q2'],
         ],
         'q1' => ['driver' => 'sync'],
         'q2' => ['driver' => 'sync'],
      ]]);
   }

   public function testDriver()
   {
      $queue = Queue::connection('rr');
      $this->assertInstanceOf(RoundRobinQueue::class, $queue);
   }

   public function testSize()
   {
      $queue = Queue::connection('rr');

      $q1 = Mockery::mock();
      $q1->shouldReceive('size')->once()->with('default')->andReturn(2);
      Queue::shouldReceive()->connection()->with('q1')->once()->andReturn($q1);
      $q2 = Mockery::mock();
      $q2->shouldReceive('size')->once()->with('default')->andReturn(3);
      Queue::shouldReceive()->connection()->with('q2')->once()->andReturn($q2);

      $this->assertEquals(5, $queue->size());
   }

   public function testSizeQueue()
   {
      $queue = Queue::connection('rr');

      $q1 = Mockery::mock();
      $q1->shouldReceive('size')->once()->with('myqueue')->andReturn(2);
      Queue::shouldReceive()->connection()->with('q1')->once()->andReturn($q1);
      $q2 = Mockery::mock();
      $q2->shouldReceive('size')->once()->with('myqueue')->andReturn(3);
      Queue::shouldReceive()->connection()->with('q2')->once()->andReturn($q2);

      $this->assertEquals(5, $queue->size('myqueue'));
   }

   public function testPush()
   {
      $queue = Queue::connection('rr');

      $q1 = Mockery::mock();
      $q1->shouldReceive('push')->once()->with('job1', '', 'default')->andReturn(1);
      $q1->shouldReceive('push')->once()->with('job3', '', 'default')->andReturn(3);
      Queue::shouldReceive()->connection()->with('q1')->twice()->andReturn($q1);
      $q2 = Mockery::mock();
      $q2->shouldReceive('push')->once()->with('job2', '', 'default')->andReturn(2);
      Queue::shouldReceive()->connection()->with('q2')->once()->andReturn($q2);

      $this->assertEquals(1, $queue->push('job1'));
      $this->assertEquals(2, $queue->push('job2'));
      $this->assertEquals(3, $queue->push('job3'));
   }

   public function testPushRaw()
   {
      $queue = Queue::connection('rr');

      $q1 = Mockery::mock();
      $q1->shouldReceive('pushRaw')->once()->with('job1', 'default', [])->andReturn(1);
      $q1->shouldReceive('pushRaw')->once()->with('job3', 'default', [])->andReturn(3);
      Queue::shouldReceive()->connection()->with('q1')->twice()->andReturn($q1);
      $q2 = Mockery::mock();
      $q2->shouldReceive('pushRaw')->once()->with('job2', 'default', [])->andReturn(2);
      Queue::shouldReceive()->connection()->with('q2')->once()->andReturn($q2);

      $this->assertEquals(1, $queue->pushRaw('job1'));
      $this->assertEquals(2, $queue->pushRaw('job2'));
      $this->assertEquals(3, $queue->pushRaw('job3'));
   }

   public function testLater()
   {
      $queue = Queue::connection('rr');

      $q1 = Mockery::mock();
      $q1->shouldReceive('later')->once()->with(1, 'job1', '',  'default')->andReturn(1);
      $q1->shouldReceive('later')->once()->with(1, 'job3', '', 'default')->andReturn(3);
      Queue::shouldReceive()->connection()->with('q1')->twice()->andReturn($q1);
      $q2 = Mockery::mock();
      $q2->shouldReceive('later')->once()->with(1, 'job2', '', 'default')->andReturn(2);
      Queue::shouldReceive()->connection()->with('q2')->once()->andReturn($q2);

      $this->assertEquals(1, $queue->later(1, 'job1'));
      $this->assertEquals(2, $queue->later(1, 'job2'));
      $this->assertEquals(3, $queue->later(1, 'job3'));
   }

   public function testPop()
   {
      $this->assertNull(Queue::connection('rr')->pop());
   }
}
