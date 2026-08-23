// Utility converter class.

export type ConvType = 'string' | 'number' | 'boolean' | 'date' | 'array' | 'object' | 'null' | 'undefined' | 'bigint' | 'symbol';

export class Conv {
  private readonly original: unknown;
  public readonly detectedType: ConvType;

  constructor(value: unknown) {
    this.original = value;
    this.detectedType = this.detectType(value);
  }

  private detectType(val: unknown): ConvType {
    if (val === null) return 'null';
    if (val === undefined) return 'undefined';
    if (Array.isArray(val)) return 'array';
    if (val instanceof Date) return 'date';
    const t = typeof val;
    if (t === 'string') return 'string';
    if (t === 'number') return 'number';
    if (t === 'boolean') return 'boolean';
    if (t === 'bigint') return 'bigint';
    if (t === 'symbol') return 'symbol';
    return 'object';
  }

  public toString(): string {
    const val = this.original;
    if (val === null) return '';
    if (val === undefined) return '';
    if (val instanceof Date) return val.toISOString();
    if (typeof val === 'number') return val.toString();
    if (typeof val === 'symbol') return val.toString();
    if (typeof val === 'object') return JSON.stringify(val);
    if (typeof val === 'string') return val;
    return '';
  }

  public toBoolean(): boolean {
    const val = this.original;
    if (typeof val === 'boolean') return val;
    if (typeof val === 'number') return val !== 0 && !Number.isNaN(val);
    if (typeof val === 'bigint') return val !== 0n;
    if (typeof val === 'string') {
      const v = val.trim().toLowerCase();
      if (v === 'false' || v === '0' || v === 'no' || v === 'n' || v === '') return false;
      return true;
    }
    if (val instanceof Date) return !Number.isNaN(val.getTime());
    if (Array.isArray(val)) return val.length > 0;
    if (val && typeof val === 'object') return Object.keys(val as object).length > 0;
    return false;
  }

  public toDate(): Date {
    const val = this.original;
    if (val instanceof Date) return new Date(val.getTime());
    if (typeof val === 'number' || typeof val === 'bigint') {
      const d = new Date(Number(val));
      if (Number.isNaN(d.getTime())) throw new Error(`Cannot convert ${String(val)} to Date`);
      return d;
    }
    if (typeof val === 'string') {
      const d = new Date(val);
      if (Number.isNaN(d.getTime())) throw new Error(`Cannot convert '${val}' to Date`);
      return d;
    }
    throw new Error(`Cannot convert type '${this.detectType(val)}' to Date`);
  }

  public toArray(): unknown[] {
    const val = this.original;
    if (Array.isArray(val)) return val.slice();
    if (val === null || val === undefined) return [];
    return [val];
  }

  public toObject(): Record<string, unknown> {
    const val = this.original;
    if (val === null || val === undefined) return {};
    if (typeof val === 'object' && !(val instanceof Date)) return { ...(val as Record<string, unknown>) };
    if (val instanceof Date) return { date: val.toISOString() };
    return { value: val };
  }

  public toNumber(): number {
    const val = this.original;
    if (val === null) return 0;
    if (val === undefined) return 0;
    if (typeof val === 'number') return val;
    if (typeof val === 'boolean') return val ? 1 : 0;
    if (typeof val === 'bigint') return Number(val);
    if (val instanceof Date) return val.getTime();
    if (typeof val === 'string') {
      const n = Number(val.trim());
      if (Number.isNaN(n)) throw new Error(`Cannot convert string '${val}' to number`);
      return n;
    }
    throw new Error(`Cannot convert type '${this.detectType(val)}' to number`);
  }

  public toInt(): number {
    const val = this.original;
    if (typeof val === 'number') return parseInt(val.toString());
    if (typeof val === 'boolean') return val ? 1 : 0;
    if (typeof val === 'bigint') return parseInt(val.toString());
    if (typeof val === 'string') return parseInt(val.trim());
    if (val === null || val === undefined) return 0;
    throw new Error(`Cannot convert type '${this.detectType(val)}' to int`);
  }

  public toFloat(): number {
    const val = this.original;
    if (typeof val === 'number') return parseFloat(val.toString());
    if (typeof val === 'boolean') return val ? 1 : 0;
    if (typeof val === 'bigint') return parseFloat(val.toString());
    if (typeof val === 'string') return parseFloat(val.trim());
    if (val === null || val === undefined) return 0;
    throw new Error(`Cannot convert type '${this.detectType(val)}' to float`);
  }

  public toBigInt(): bigint {
    const val = this.original;
    if (typeof val === 'bigint') return val;
    if (typeof val === 'number') return BigInt(Math.trunc(val));
    if (typeof val === 'string') {
      try {
        return BigInt(val.trim());
      } catch {
        throw new Error(`Cannot convert string '${val}' to bigint`);
      }
    }
    throw new Error(`Cannot convert type '${this.detectType(val)}' to bigint`);
  }
}

export function c(value: unknown): Conv {
  return new Conv(value);
}
