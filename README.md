# MECO PHP - Mongolian Encoding Converter

蒙古文编码转换器 PHP 版本

> **⚠️ Notice / 说明**
>
> This repository is a PHP port aligned against the original Java library [east-mod/meco](https://github.com/east-mod/meco).
>
> 本仓库是对原始 Java 库 [east-mod/meco](https://github.com/east-mod/meco) 的 PHP 移植版本，并以与原库行为对齐为目标。
>
> The recent alignment and validation work in this repository was done in **Windsurf IDE**.
>
> 最近这一轮对齐与修复工作是在 **Windsurf IDE** 中完成的。

## Project Goal / 项目目标

- **English**  
  Keep this PHP implementation aligned with the original Java library as closely as possible.

- **中文**  
  尽量让这个 PHP 实现与原始 Java 库保持一致。

## Current Status / 当前状态

- **English**  
  Basic conversion paths are implemented and basic fixture-based tests have been added. The current expectation is that the main supported paths should work. If you still find a mismatch or runtime problem, please open an issue.

- **中文**  
  目前基础转换路径已经实现，并补充了基于 fixture 的基础测试。当前预期主干支持路径应当可以正常工作；如果你仍然发现结果不一致或运行问题，请提 issue。

## Requirements / 运行要求

- PHP >= 7.2
- ext-mbstring

## Installation / 安装

### Standalone autoload / 直接使用项目自带 autoload

```php
require_once 'path/to/meco_php/autoload.php';
```

### Composer / 使用 Composer

```bash
composer install
```

## Usage / 使用方式

```php
<?php
require_once 'autoload.php';

use Meco\Enums\CodeType;
use Meco\TranslateService;

$result = TranslateService::translate(
    CodeType::DELEHI,
    CodeType::ZVVNMOD,
    'ᠮᠣᠩᠭᠣᠯ'
);

$result = TranslateService::translate(
    CodeType::MENK_LETTER,
    CodeType::ZVVNMOD,
    $menkInput
);

$result = TranslateService::translate(
    CodeType::Z52,
    CodeType::ZVVNMOD,
    $z52Input
);

$result = TranslateService::translate(
    CodeType::ZVVNMOD,
    CodeType::DELEHI,
    $zvvnmodInput
);
```

## Supported Encodings / 支持的编码

| Encoding | Type | Description |
|----------|------|-------------|
| Zvvnmod | Shape | Intermediate shape encoding / 中间字形编码 |
| Delehi | Letter | Standard Mongolian letter encoding implemented by Delehi Company / Delehi公司实现的标准蒙古文字母编码 |
| MenkShape | Shape | Menk shape encoding / 蒙科字形编码 |
| MenkLetter | Letter | Standard Mongolian letter encoding implemented by MenkSoft Company / MenkSoft公司实现的标准蒙古文字母编码 |
| Z52 | Shape | Z52 shape encoding / Z52 字形编码 |
| Oyun | Letter | Not implemented separately; behavior is close to Delehi in practice / 未单独实现，实践中与 Delehi 行为接近 |

## Notes on Standard Mongolian Encodings / 关于标准蒙古文编码的说明

- **English**  
  In practical use, `Delehi`, `MenkLetter`, and `Oyun` can all be regarded as standard Mongolian letter encodings in the context of GB/T 2010 and Unicode 2010 era implementations. Because some definitions were interpreted differently by different vendors, real-world behavior can vary across implementations.

- **中文**  
  在实际使用中，`Delehi`、`MenkLetter`、`Oyun` 理论上都可以视为国标 2010 和 Unicode 2010 语境下的标准蒙古文字母编码实现。由于其中部分定义存在歧义，不同厂商的实现之间会出现一些差异。

- **English**  
  In this repository, `Delehi` and `Oyun` are considered very close in practical behavior, so `Oyun` is not implemented as a separate conversion path at the moment.

- **中文**  
  在本仓库中，`Delehi` 与 `Oyun` 在实际行为上被视为非常接近，因此当前没有把 `Oyun` 单独实现为一条独立转换路径。

## Conversion Status / 转换状态

| Path | Status |
|------|--------|
| Delehi → Zvvnmod | ✅ |
| Zvvnmod → Delehi | ✅ |
| Menk Letter → Zvvnmod | ✅ |
| Menk Shape → Zvvnmod | ✅ |
| Z52 → Zvvnmod | ✅ |
| Zvvnmod → Menk Letter | ✅ |
| Zvvnmod → Menk Shape | ✅ |
| Zvvnmod → Z52 | ✅ |
| Oyun ↔ Zvvnmod | ❌ |

## Architecture / 架构

```text
Source Encoding -> Zvvnmod -> Target Encoding
```

- **English**  
  All conversions are normalized through `Zvvnmod` as the intermediate representation.

- **中文**  
  所有编码转换都先归一到 `Zvvnmod`，再转换到目标编码。

## Testing / 测试

### Basic fixture test / 基础 fixture 测试

```bash
php tests/test_text_fixtures.php
```

- **English**  
  The test data is defined in `tests/Text.php`, and the runnable test script is `tests/test_text_fixtures.php`.

- **中文**  
  测试数据定义在 `tests/Text.php`，可执行测试脚本是 `tests/test_text_fixtures.php`。

## Alignment Policy / 对齐策略

- **English**  
  This repository should stay aligned with the original Java implementation. If the PHP result differs from the original library and the Java behavior is considered correct, the fix should be made in this PHP repository.

- **中文**  
  本仓库应尽量与原始 Java 实现保持一致。如果 PHP 的结果与原库不一致，并且 Java 的行为是正确的，那么应当在这个 PHP 仓库中修复对齐问题。

- **English**  
  If the mismatch comes from a confirmed issue in the original Java library itself, the real fix should be made in the original upstream repository. In that case, this PHP repository can track the problem by opening an issue and linking the upstream issue, commit, or discussion.

- **中文**  
  如果差异来源于原始 Java 库本身的问题，那么真正的修复应当在原库中完成。在这种情况下，可以在当前 PHP 仓库提 issue，并引用原库对应的 issue、commit 或讨论链接。

- **English**  
  Conversion rules related to newer standards, including 2023-era standard updates, are currently not implemented in the original upstream library. If upstream adds them in the future, this PHP repository should follow and stay aligned.

- **中文**  
  与更新标准相关的转换规则，包括 2023 年标准方向的支持，目前原始上游库还没有实现。如果后续原库实现了，这个 PHP 仓库也应继续对齐。

## Issue Policy / Issue 处理建议

- **English**
  - If you find a bug in this PHP port, open an issue in this repository.
  - If the bug is actually inherited from upstream, open or reference the upstream issue as well.
  - When possible, include input text, expected output, actual output, and the corresponding upstream Java reference.

- **中文**
  - 如果你发现的是这个 PHP 移植版的问题，请在当前仓库提 issue。
  - 如果问题其实来自原库，也请补充或引用原库的 issue。
  - 最好同时附上输入文本、期望输出、实际输出，以及对应的 Java 参考位置。

## Rule and Mapper Generators / Rule 与 Mapper 生成脚本

### Rule generator / Rule 生成脚本

```bash
php tools/generate_rules.php
```

- **English**  
  This script regenerates PHP `TranslateRule` classes from the Java rule sources in the original library.

- **中文**  
  这个脚本会根据原始 Java 库中的 rule 源码，重新生成 PHP 的 `TranslateRule` 类。

### Mapper generator / Mapper 生成脚本

```bash
php tools/generate_mappers.php
```

- **English**  
  This script regenerates PHP mapper classes from the Java mapper sources in the original library.

- **中文**  
  这个脚本会根据原始 Java 库中的 mapper 源码，重新生成 PHP 的 mapper 类。

### Important note / 重要说明

- **English**
  - The generator scripts assume the original Java project is available locally in the expected relative path.
  - Generated rule and mapper files may be overwritten when the scripts are rerun.
  - If a generated result is wrong, prefer fixing the generator script instead of manually patching generated files.

- **中文**
  - 这些生成脚本默认原始 Java 项目已经在本地，并且位于预期的相对路径上。
  - 重新执行脚本时，已生成的 rule 和 mapper 文件可能会被覆盖。
  - 如果生成结果有问题，优先修复生成脚本，而不是长期手工修改生成文件。

## Standards References / 标准参考链接

- **English**
  - Unicode Mongolian code chart: <https://www.unicode.org/charts/PDF/U1800.pdf>
  - Unicode Mongolian document list: <http://www.unicode.org/L2/topical/mongolian/>
  - Information technology—Traditional Mongolian nominal characters, presentation characters and use rules of controlling characters (GB/T 25914-2010): <https://openstd.samr.gov.cn/bzgk/gb/newGbInfo?hcno=62808E0BCB8246A287CFD9CF795ECF94>
  - Information technology—Traditional Mongolian nominal characters, presentation characters and use rules of controlling characters (GB/T 25914-2023): <https://openstd.samr.gov.cn/bzgk/gb/newGbInfo?hcno=BD6429DE5A7FC782FAAE13938A07166E>



- **中文**
  - Unicode 蒙古文编码表：<https://www.unicode.org/charts/PDF/U1800.pdf>
  - Unicode 蒙古文相关文档列表：<http://www.unicode.org/L2/topical/mongolian/>
  - 信息技术 传统蒙古文名义字符、变形显现字符和控制字符使用规则（GB/T 25914-2010）：<https://openstd.samr.gov.cn/bzgk/gb/newGbInfo?hcno=62808E0BCB8246A287CFD9CF795ECF94>
  - 信息技术 传统蒙古文名义字符、变形显现字符和控制字符使用规则（GB/T 25914-2023）：<https://openstd.samr.gov.cn/bzgk/gb/newGbInfo?hcno=BD6429DE5A7FC782FAAE13938A07166E>

## Project Structure / 项目结构

```text
src/
├── Enums/                  # Enum definitions / 枚举
├── Exception/              # Exceptions / 异常
├── Helper/                 # Helpers / 工具
├── Unicode/                # Unicode helpers / Unicode 工具
├── Word/                   # Word and fragment models / 词与片段模型
├── Rules/                  # Generated and supporting rules / 规则
│   ├── Delehi/
│   ├── Menk/
│   └── Z52/
├── Translator/             # Translators / 转换器
└── TranslateService.php    # Public entry / 主入口

tools/
├── generate_rules.php      # Generate PHP rules from Java / 从 Java 生成 PHP rule
└── generate_mappers.php    # Generate PHP mappers from Java / 从 Java 生成 PHP mapper

tests/
├── Text.php                # Fixture data / 测试数据
└── test_text_fixtures.php  # Runnable fixture test / 可执行测试
```

## License / 许可协议

- **English**  
  This project is licensed under the **Apache License 2.0**, inheriting and aligning with the license of the original `east-mod/meco` Java library.

- **中文**  
  本项目基于 **Apache License 2.0** 协议开源，与原始的 `east-mod/meco` Java 库保持一致的许可。

- **English**  
  In practice, this means the repository is highly open: you are free to use it commercially, modify it, distribute it, and integrate it into your own projects, provided you include the original copyright notice and state any changes.

- **中文**  
  从实际使用角度来说，这意味着本仓库极其开放：你可以自由地用于商业项目、修改、分发并集成到自己的项目中，只需保留原版权声明和许可协议即可。
## Acknowledgments / 致谢

- **English**  
  This PHP port was created by **satsrag**, but is entirely based on the original [east-mod/meco](https://github.com/east-mod/meco) library created by **zorigt (east-mod)**. The core rules, mappers, and architectural design belong to the original author.

- **中文**  
  这个 PHP 移植版本由 **satsrag** 创建，但它完全基于 **zorigt (east-mod)** 编写的原始 [east-mod/meco](https://github.com/east-mod/meco) 库。核心转换规则、映射表以及架构设计均归功于原作者。
