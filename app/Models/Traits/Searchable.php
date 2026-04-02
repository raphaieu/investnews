<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Searchable
{
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $term = preg_replace('/\s+/', ' ', $term) ?: '';
        $tokens = array_values(array_filter(preg_split('/\s+/', $term) ?: []));

        $columns = $this->getSearchableColumns();
        $relations = $this->getSearchableRelations();

        $matchToken = function (Builder $builder, string $token) use ($columns, $relations): void {
            foreach ($columns as $i => $column) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $builder->{$method}($column, 'like', "%{$token}%");
            }

            foreach ($relations as $relation => $relationColumns) {
                $builder->orWhereHas($relation, function (Builder $relationQuery) use ($token, $relationColumns) {
                    $relationQuery->where(function (Builder $q) use ($token, $relationColumns) {
                        foreach ($relationColumns as $i => $column) {
                            $method = $i === 0 ? 'where' : 'orWhere';
                            $q->{$method}($column, 'like', "%{$token}%");
                        }

                        $tokenSlug = Str::slug($token);
                        if ($tokenSlug !== '' && in_array('slug', $relationColumns)) {
                            $q->orWhere('slug', 'like', "%{$tokenSlug}%");
                        }
                    });
                });
            }
        };

        return $query->where(function (Builder $q) use ($term, $tokens, $matchToken, $columns, $relations) {
            // Match da frase completa
            $q->where(function (Builder $phraseQuery) use ($term, $columns, $relations) {
                foreach ($columns as $i => $column) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $phraseQuery->{$method}($column, 'like', "%{$term}%");
                }

                foreach ($relations as $relation => $relationColumns) {
                    $phraseQuery->orWhereHas($relation, function (Builder $relationQuery) use ($term, $relationColumns) {
                        $relationQuery->where(function (Builder $q) use ($term, $relationColumns) {
                            foreach ($relationColumns as $i => $column) {
                                $method = $i === 0 ? 'where' : 'orWhere';
                                $q->{$method}($column, 'like', "%{$term}%");
                            }

                            $termSlug = Str::slug($term);
                            if ($termSlug !== '' && in_array('slug', $relationColumns)) {
                                $q->orWhere('slug', 'like', "%{$termSlug}%");
                            }
                        });
                    });
                }
            });

            // OU match de todos os tokens
            if (count($tokens) > 1) {
                $q->orWhere(function (Builder $tokenGroupQuery) use ($tokens, $matchToken) {
                    foreach ($tokens as $token) {
                        $tokenGroupQuery->where(function (Builder $tokenQuery) use ($matchToken, $token) {
                            $matchToken($tokenQuery, $token);
                        });
                    }
                });
            }
        });
    }

    protected function getSearchableColumns(): array
    {
        return $this->searchable ?? [];
    }

    protected function getSearchableRelations(): array
    {
        return $this->searchableRelations ?? [];
    }
}
