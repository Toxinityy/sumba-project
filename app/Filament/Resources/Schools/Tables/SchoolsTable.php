<?php

namespace App\Filament\Resources\Schools\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/*
 | The list Vera lands on. Two things have to be readable at a glance: which
 | school this is, and whether it is live. Everything translated is stored as
 | JSON, so name and location are read per locale (`name->id`) — both a
 | column and a search have to say which locale they mean.
 |
 | "Terjemahan" is the completeness badge spec §7 asks for: a school with no
 | English yet renders Indonesian on /en with a note, which is intended, but
 | the editor should be able to see it from the list.
 */
class SchoolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name.id')
                    ->label('Nama')
                    ->weight('bold')
                    ->description(fn ($record) => $record->location['id'] ?? null)
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where('name->id', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")),

                TextColumn::make('level')->label('Jenjang')->badge(),

                TextColumn::make('pupils')->label('Murid')->numeric()->sortable(),

                IconColumn::make('published_at')
                    ->label('Terbit')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isPublished()),

                TextColumn::make('name.en')
                    ->label('Terjemahan')
                    ->badge()
                    ->color(fn ($state) => filled($state) ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => filled($state) ? 'ID + EN' : 'ID saja'),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TernaryFilter::make('published_at')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah terbit')
                    ->falseLabel('Draf')
                    ->queries(
                        true: fn (Builder $query) => $query->published(),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                    ),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah'),
            ]);
    }
}
