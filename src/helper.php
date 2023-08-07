<?php

function async(Closure $closure, mixed ...$args): Amp\Future {
    return Amp\async($closure, ...$args);
}

function await(Amp\Future $future): mixed {
    return $future->await();
}

function delay(float $timeout, bool $reference = true, Amp\Cancellation $cancellation = null): void {
    Amp\delay($timeout, $reference, $cancellation);
}

?>