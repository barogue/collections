<?php

return [
    'minimum_target_php_version' => '8.2',
    'target_php_version' => '8.2',
    'directory_list' => [
        'src',
        'vendor/barogue'
    ],
    "exclude_analysis_directory_list" => [
        'vendor/'
    ],
    'plugins' => [
        'AlwaysReturnPlugin',
        'AvoidableGetterPlugin',
        'ConstantVariablePlugin',
        'DollarDollarPlugin',
        'LoopVariableReusePlugin',
        'RedundantAssignmentPlugin',
        'RemoveDebugStatementPlugin',
        'ShortArrayPlugin',
        'SimplifyExpressionPlugin',
        'UnreachableCodePlugin',
        'UnsafeCodePlugin',
        'WhitespacePlugin',
    ],
];