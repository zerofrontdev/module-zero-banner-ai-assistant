<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */
declare(strict_types=1);

namespace Zero\BannerAiAssistant\Test\Unit\Model\Tool;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zero\AiAssistant\Model\Tool\ToolInterface;
use Zero\BannerAiAssistant\Model\Tool\ListBanners;
use Zero\BannerAiAssistant\Model\Tool\SaveSlide;

/**
 * The banner tools keep their names, ACL resources, areas and "changes" flags when they move to this bridge:
 * the role's saved ACL rules, the stored proposals and Claude's tool list depend on them.
 */
class ToolsContractTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string<ToolInterface>, 1: string, 2: string, 3: bool}>
     */
    public static function tools(): array
    {
        return [
            'list_banners' => [ListBanners::class, 'list_banners', 'Zero_Banner::banner', false],
            'save_slide' => [SaveSlide::class, 'save_slide', 'Zero_Banner::banner_save', true],
        ];
    }

    #[DataProvider('tools')]
    public function testNameResourceAreaAndChanges(string $class, string $name, string $resource, bool $changes): void
    {
        $arguments = [];
        foreach ((new \ReflectionClass($class))->getConstructor()?->getParameters() ?? [] as $parameter) {
            $arguments[] = $this->createStub((string) $parameter->getType());
        }
        /** @var ToolInterface $tool */
        $tool = new $class(...$arguments);

        $this->assertSame($name, $tool->name());
        $this->assertSame($resource, $tool->resource());
        $this->assertSame('banners', $tool->area());
        $this->assertSame($changes, $tool->changes());
    }

    public function testTheToolsNeedNoObjectManager(): void
    {
        foreach (self::tools() as [$class]) {
            foreach ((new \ReflectionClass($class))->getConstructor()?->getParameters() ?? [] as $parameter) {
                $this->assertStringNotContainsString('ObjectManager', (string) $parameter->getType(), $class);
            }
        }
    }
}
