<?php
namespace Sete\V1\Rest\Dashboard;

class DashboardResourceFactory
{
    public function __invoke($services)
    {
        return new DashboardResource();
    }
}
