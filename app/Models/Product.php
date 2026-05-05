<?php

namespace App\Models;

use App\Enums\AffiliateCommissionType;
use App\Enums\ContributorCommissionBase;
use App\Enums\ContributorCommissionType;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Fillable([
    'product_category_id',
    'title',
    'slug',
    'short_description',
    'full_description',
    'product_type',
    'thumbnail',
    'price',
    'sale_price',
    'status',
    'visibility',
    'access_type',
    'stock',
    'quota',
    'publish_at',
    'is_featured',
    'is_affiliate_enabled',
    'landing_page_enabled',
    'landing_page_zip_path',
    'landing_page_extract_path',
    'landing_page_entry_file',
    'landing_page_asset_token',
    'landing_page_uploaded_at',
    'landing_page_meta_title',
    'landing_page_meta_description',
    'landing_page_version',
    'affiliate_commission_type',
    'affiliate_commission_value',
    'is_contributor_commission_enabled',
    'contributor_user_id',
    'contributor_commission_type',
    'contributor_commission_value',
    'contributor_commission_base',
    'sort_order',
    'metadata',
    'visibility_mode',
    'purchase_mode',
    'access_mode',
    'allowed_viewer_types',
    'allowed_buyer_types',
    'allowed_access_types',
    'allowed_role_ids',
    'allowed_user_ids',
    'ineligible_message',
    'hidden_from_marketplace',
])]
class Product extends Model
{
    use SoftDeletes;

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @return HasMany<ProductFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class);
    }

    public function course(): HasOne
    {
        return $this->hasOne(Course::class);
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class);
    }

    public function getThumbnailUrl(): ?string
    {
        if (! filled($this->thumbnail)) {
            return null;
        }

        if (Str::startsWith($this->thumbnail, ['http://', 'https://'])) {
            return $this->thumbnail;
        }

        $normalized = ltrim($this->thumbnail, '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = ltrim(Str::after($normalized, 'storage/'), '/');
        }

        if (Str::startsWith($normalized, 'public/')) {
            $normalized = ltrim(Str::after($normalized, 'public/'), '/');
        }

        return asset('storage/'.$normalized);
    }

    /**
     * @return HasMany<UserProduct, $this>
     */
    public function userProducts(): HasMany
    {
        return $this->hasMany(UserProduct::class);
    }

    /** @return BelongsTo<User, $this> */
    public function contributorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_user_id');
    }

    /** @return HasMany<ContributorCommission, $this> */
    public function contributorCommissions(): HasMany
    {
        return $this->hasMany(ContributorCommission::class);
    }

    /**
     * Produk-produk yang termasuk dalam bundle ini.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function bundledProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_bundles',
            'bundle_product_id',
            'bundled_product_id',
        )->withPivot(['sort_order'])->withTimestamps()->orderByPivot('sort_order');
    }

    /**
     * Bundle-bundle yang memasukkan produk ini.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function parentBundles(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_bundles',
            'bundled_product_id',
            'bundle_product_id',
        )->withPivot(['sort_order'])->withTimestamps();
    }

    public function scopePublished(Builder $query): void
    {
        $query
            ->where('status', ProductStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('publish_at')->orWhere('publish_at', '<=', Carbon::now());
            });
    }

    public function scopeVisiblePublic(Builder $query): void
    {
        $query->where('visibility', ProductVisibility::Public);
    }

    public function scopeNotHiddenFromMarketplace(Builder $query): void
    {
        $query->where('hidden_from_marketplace', false);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    protected function effectivePrice(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $price = (string) $this->price;
                $salePrice = $this->sale_price !== null ? (string) $this->sale_price : null;

                if ($salePrice === null) {
                    return $price;
                }

                if ((float) $salePrice <= 0) {
                    return $price;
                }

                return (float) $salePrice < (float) $price ? $salePrice : $price;
            },
        );
    }

    protected function hasDiscount(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->sale_price !== null && (float) $this->sale_price > 0 && (float) $this->sale_price < (float) $this->price,
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,
            'status' => ProductStatus::class,
            'visibility' => ProductVisibility::class,
            'access_type' => ProductAccessType::class,
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'quota' => 'integer',
            'publish_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_affiliate_enabled' => 'boolean',
            'landing_page_enabled' => 'boolean',
            'landing_page_uploaded_at' => 'datetime',
            'landing_page_version' => 'integer',
            'affiliate_commission_type' => AffiliateCommissionType::class,
            'affiliate_commission_value' => 'decimal:2',
            'is_contributor_commission_enabled' => 'boolean',
            'contributor_commission_type' => ContributorCommissionType::class,
            'contributor_commission_value' => 'decimal:2',
            'contributor_commission_base' => ContributorCommissionBase::class,
            'sort_order' => 'integer',
            'metadata' => 'array',
            'allowed_viewer_types' => 'array',
            'allowed_buyer_types' => 'array',
            'allowed_access_types' => 'array',
            'allowed_role_ids' => 'array',
            'allowed_user_ids' => 'array',
            'hidden_from_marketplace' => 'boolean',
        ];
    }
}
