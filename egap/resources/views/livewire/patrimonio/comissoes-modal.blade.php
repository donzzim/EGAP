<div class="materiais-termo-modal w-full">
    {{ $this->table }}

    <style>
        .materiais-termo-modal-window .fi-modal-content {
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .materiais-termo-modal,
        .materiais-termo-modal .fi-ta,
        .materiais-termo-modal .fi-ta-ctn {
            height: 100%;
            min-height: 0;
        }

        .materiais-termo-modal .fi-ta-ctn {
            display: flex;
            flex-direction: column;
        }

        .materiais-termo-modal .fi-ta-content {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
        }

        .materiais-termo-modal .fi-ta-pagination {
            flex-shrink: 0;
        }
    </style>
</div>
